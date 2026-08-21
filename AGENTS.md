# Project guide for AI agents

## What this project is

An e-commerce solution for small to mid-sized businesses, launching for small
businesses first.

**Current stage: architecture only. No application code exists yet.**

**Stack:** Laravel (PHP) for the API, React for the UI as a separate client
consuming it, PostgreSQL for the database. See
`docs/decisions/0001-stack.md` for the reasoning and the conditions it
depends on.

## Source of truth

| Document | What it covers |
| --- | --- |
| `Dev/system-design.txt` | The architecture and database design. Authoritative and stack-independent. |
| `Dev/change-guide.txt` | Procedures for adding modules, features, and database changes. Authoritative. |
| `Dev/laravel-guardrails.txt` | How each architectural rule is achieved in Laravel, and which Laravel idioms are forbidden as a result. |
| `docs/decisions/` | One short record per decision made. |
| `api-dev.txt`, `database.txt`, `Features-list.txt` | Earlier feature-level drafts. Useful background, **not** authoritative on architecture. Where they conflict with `Dev/`, `Dev/` wins. |

Read the relevant document before proposing architecture, database, API, or
testing decisions. Do not re-derive answers that are already written down.

## Keep these topics separate

This is the mistake to avoid in this repo. Three distinct conversations:

1. **Architecture** — the abstract shape of the system. Decided; see `Dev/`.
2. **Modules** — which capabilities exist. **Not decided yet.**
3. **Features and requirements** — what each capability does. Not decided yet.

When asked about one, do not answer with another. In particular: architecture
documentation must not name modules or features. Use placeholder names
(Module A, schema `alpha`, table `widget`) as the existing documents do.

## The architecture — decided, do not re-open

**A modular monolith with one schema per module, built so any module can be
extracted into its own service later without a rewrite.**

One deployable application. Several self-contained modules inside it. One
PostgreSQL database in which each module owns a private schema with its own
database roles.

Three sub-decisions come with it:

- **Inside a module** — four layers (api, application, domain,
  infrastructure) with dependencies pointing inward, files grouped by use
  case. Clean/hexagonal architecture with vertical slices.
- **Between modules** — synchronous contract call when the answer is needed
  to finish the request; event when announcing that something happened.
- **In the database** — no foreign keys across schemas, one writer per table,
  cross-schema SQL only in read-only `reporting` views.

Rejected alternatives, with reasoning in `Dev/system-design.txt` Part 2.3: a
layered monolith (no seam to cut later), a modular monolith on a shared schema
(code boundaries without data boundaries do not survive), a few coarse
services, and fine-grained microservices (both are the natural next step, not
the starting point). Do not propose these unless the decision is explicitly
reopened.

## Non-negotiable rules

These are the five laws from the design document. Never break them, and flag
any request that would.

1. **One owner.** Every table belongs to exactly one module. Only that module
   writes it or migrates it.
2. **Published surface only.** A module may use another module's published
   contract. Never its classes, its tables, or its HTTP routes.
3. **One direction.** Dependencies point one way. No cycles, ever.
4. **Outside goes behind a port.** Any external service is used through an
   interface the module defines, implemented by an adapter.
5. **Additive first.** Every change to a published thing — column, contract,
   event, endpoint — is additive first. Removal is a separate, later step.
   Nothing is renamed in place.

## Database rules

- One database. One schema per module, plus `platform` (machinery) and
  `reporting` (read-only cross-schema views). `public` stays empty.
- **Inside a schema:** real foreign keys, constraints, cascades, joins. Use
  them freely.
- **Across schemas, current value needed:** store the bare id, **no foreign
  key**, index the column, resolve through the owner's contract. Always
  comment the column explaining why there is no constraint.
- **Across schemas, historical value needed:** copy the value in at the time
  of the event and never update the copy.
- Cross-schema SQL is legal in exactly one place: `reporting` views.
- One transaction per module. Cross-schema transactions require a documented,
  tested exception.
- Migrations live in the owning module and touch one schema. Never rename,
  retype, or set `NOT NULL` directly — use expand-contract
  (`Dev/change-guide.txt` Part 4.5).
- Keys: `id UUID` time-ordered (UUIDv7). Money: integer minor units plus an
  explicit currency code, never a float. Time: `TIMESTAMPTZ` in UTC.

## Code structure

Every module has the same shape: a public `Contracts` build unit (interfaces,
DTOs, events) and a private one containing `Api`, `Application`, `Domain`,
`Infrastructure`. Dependencies point inward; `Domain` depends on nothing.

Group files by use case, not by technical type — one folder per use case
holding its command, handler, validator and result. See
`Dev/system-design.txt` Part 4 for the full tree and
`Dev/change-guide.txt` Part 3.2 for where each kind of file goes.

In Laravel this means one Composer path package per module plus a separate
`-contracts` package. Domain code lives under `modules/`, never in `app/`;
`app/` holds host concerns only — middleware, providers, exception handling.
The Laravel-specific tree is in `Dev/laravel-guardrails.txt` Part 3.

## Laravel: forbidden, though idiomatic

Each of these is normal Laravel and each breaks a rule above. This is the
cost of the framework choice, and it is why Deptrac and the Pest
architecture suite must block the build.

- **Eloquent models outside `Infrastructure/Persistence`.** Repositories map
  models to domain objects. A model never appears in a controller, handler,
  domain class, DTO, event, or API resource.
- **Eloquent relations crossing a module boundary.** Inside a module they're
  fine and encouraged. Across modules, `$widget->subscription` is the
  cross-schema join we forbade, in one idiomatic line no tool objects to.
- **Route model binding.** Bind the id; load through the repository.
- **`DB::` or the query builder against another module's tables.** The
  per-schema database role also makes this fail at runtime, by design.
- **Facades and global helpers in Domain or Application** — `auth()`,
  `request()`, `config()`, `now()`, `DB::`, `Cache::`, `Http::`. They are
  invisible dependencies. Inject instead. Fine in Infrastructure and Api.
- **`->foreign()` across schemas**, and **`renameColumn`** on a live table.
- **A global `app/Models` folder.** Unowned data.
- **Observers or global scopes reaching into another module.**
- **`paginate()` on lists that grow** — use `cursorPaginate()`.
- **Business rules enforced only in a FormRequest.** The module is also
  reachable through its contract, where no FormRequest runs.

If one of these looks like the only reasonable answer, raise it rather than
doing it — it's a signal about the boundaries, not permission.

## Module communication

Ask one question: do I need the answer before I can finish this request?

- **Yes** → synchronous call to the other module's published contract.
  Always batch-shaped; never called inside a loop.
- **No** → publish an event. Past tense, immutable, handlers idempotent,
  publisher never depends on the outcome.

## API conventions

Resource-oriented HTTP, JSON, contract-first. Path-major versioning
(`/api/v1/`), additive-only within a version. Cursor pagination, not offset.
`Idempotency-Key` on mutating writes. One error shape everywhere: RFC 9457
problem details, where clients switch on a stable `type` URI. Details in
`Dev/system-design.txt` Part 5.

## Testing

Confidence comes from module integration tests against a real containerised
database — not from mocks. Fake only what is outside the process (providers,
mail, storage); never fake the database, the event bus, or the module under
test. Architecture tests enforce the rules above and are not optional.
Details in `Dev/system-design.txt` Part 6.

## Working style in this repo

- Answer the question that was asked. Do not expand the scope.
- Plain language over jargon. Short over long.
- Do not invent modules, features, or requirements that have not been decided.
- When a decision is made, record it in `docs/decisions/` in a few lines:
  the choice, the alternatives, why.
- If a request conflicts with a rule above, say so and explain the cost
  rather than quietly complying.
