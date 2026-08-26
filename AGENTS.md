# Project guide for AI agents

## What this project is

An e-commerce solution for small to mid-sized businesses, launching for small
businesses first.

**Current stage: architecture only. No application code exists yet.**

**Stack:** Laravel (PHP) for the API, PostgreSQL for the database. Two front
ends: Blade server-rendered for the public storefront with React islands, and
a React SPA (Vite + TanStack Query/Router) for the admin behind a login. See
`doc/architecture-map.txt` and `doc/tech-stack.txt`.

## Source of truth

| Document | What it covers |
| --- | --- |
| `doc/architecture-map.txt` | The architecture: diagrams for database, modules, API, frontend. Authoritative. |
| `doc/folder-structure.txt` | Where every file goes. Nested contracts. `frontend/` is top-level. |
| `doc/module-guideline.txt` | How to build or change a module, and the done checklists. |
| `doc/dev-workflow.txt` | How we build with an AI assistant: six stages, prompts, tests, review. |
| `doc/tech-stack.txt` | Versions, tooling, and what is not decided yet. |

Read the relevant document before proposing architecture, database, API, or
testing decisions. Do not re-derive answers that are already written down.

## Keep these topics separate

This is the mistake to avoid in this repo. Three distinct conversations:

1. **Architecture** — the abstract shape of the system. Decided; see `doc/architecture-map.txt`.
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

Rejected alternatives, with reasoning in `doc/architecture-map.txt` section 2: a
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
  (`doc/module-guideline.txt` section 9).
- Keys: `id UUID` time-ordered (UUIDv7). Money: integer minor units plus an
  explicit currency code, never a float. Time: `TIMESTAMPTZ` in UTC.

## Code structure

Every module has the same shape: a public `Contracts` build unit (interfaces,
DTOs, events) and a private one containing `Api`, `Application`, `Domain`,
`Infrastructure`. Dependencies point inward; `Domain` depends on nothing.

Group files by use case, not by technical type — one folder per use case
holding its command, handler, validator and result. See
`doc/folder-structure.txt` for the tree and `doc/module-guideline.txt` for
where each kind of file goes.

In Laravel this means one Composer path package per module. Contracts
live in `src/Contracts/` inside that package, not as a second package.
Domain code lives under `modules/`, never in `app/`; `app/` holds host
concerns only. All UI lives under `frontend/`, outside every module.

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
`doc/architecture-map.txt` section 6.

Routes are defined inside the module that owns them, but there is still one
router — the host mounts, it does not define. **Module names never appear in
URLs**; the API is one coherent surface and module boundaries are invisible
from outside. An endpoint whose only job is composing several modules' data
for a screen belongs to the thin composition layer: contracts only, no
tables, no business rules. An endpoint that *writes* to several modules is a
design smell — one decision belongs to the owning module, the consequences
are events.

## Frontend

Two surfaces, split by whether a crawler sees the page. Blade server-rendered
for the public storefront, with React islands only where a part of the page is
genuinely interactive. A React SPA (Vite, TanStack Query, TanStack Router) for
the admin behind a login.

**All UI lives in one top-level `frontend/` directory** — `frontend/storefront/`
(Blade views, thin controllers, island sources) and `frontend/admin/` (the
SPA). Not in `resources/views`, and never inside a module. A module's `Api`
layer serves the JSON API only. Deptrac covers `frontend/` as its own layer,
allowed to reach `shared` and `*-contracts` and nothing else.

- A storefront controller calls **module contracts only** — never an Eloquent
  model, a query builder, or a table name, and it holds no business rules. It
  is the thin composition layer.
- React holds no database credentials, no ORM, and no business rules. A rule
  enforced only in the frontend is not enforced.
- Blade calls contracts in-process. React speaks HTTP to `/api/v1` only, and
  never calls the app's own API from the server.
- One composition endpoint per screen, not eight calls from the browser.

The map is `doc/architecture-map.txt` section 8.

## Testing

Confidence comes from module integration tests against a real containerised
database — not from mocks. Fake only what is outside the process (providers,
mail, storage); never fake the database, the event bus, or the module under
test. Architecture tests enforce the rules above and are not optional.
Strategy and how-to in `doc/architecture-map.txt` section 10 and
`doc/dev-workflow.txt`.

## Working style in this repo

- Answer the question that was asked. Do not expand the scope.
- Plain language over jargon. Short over long.
- Do not invent modules, features, or requirements that have not been decided.
- If a request conflicts with a rule above, say so and explain the cost
  rather than quietly complying.
