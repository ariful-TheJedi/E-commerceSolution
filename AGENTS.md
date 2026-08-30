# Project guide for AI agents

## What this project is

A **modular monolith host** you can copy for any product. Domain code does not
belong in the host. Put the product name and capabilities in
`doc/Features-list.txt`, then add modules one at a time.

**Current stage:** **Product** — requirements analyzed (R1); catalog list
F1–F15 (WooCommerce small/mid). Folder emptied. Skeleton (R2) and module
docs (R2b) to rebuild. Wall test (R3) not run yet. F1–F15 and A1–A5 open.
One feature / use case per turn. Always flip checkboxes in
`doc/Features-list.txt` **and** this file when work completes.

**Stack:** Laravel (PHP) for the API, PostgreSQL for the database. Two front
ends: Blade server-rendered for the public site with React islands, and
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
| `doc/Features-list.txt` | **This product:** modules, per-module features, status, out of scope. Authoritative for scope. |

Read the relevant document before proposing architecture, database, API, or
testing decisions. Do not re-derive answers that are already written down.
When the developer names a module or feature, update **both**
`doc/Features-list.txt` and `AGENTS.md` in that turn. Never invent modules
or features.

## Keep these topics separate

This is the mistake to avoid in this repo. Three distinct conversations:

1. **Architecture** — the abstract shape of the system. Decided; see `doc/architecture-map.txt`.
2. **Modules** — which capabilities exist. Listed in `doc/Features-list.txt` (partially decided).
3. **Features and requirements** — what each capability does. Listed under each module in `doc/Features-list.txt`.

When asked about one, do not answer with another. Architecture documentation
must not name real modules or features — use placeholders there (Module A,
schema `alpha`, table `widget`). Product scope lives only in
`doc/Features-list.txt` (and the summary below, which must stay in sync).

## Product module

Folder: `modules/product` · Schema: `product` · Status: in progress (files wiped; rebuild next)

**Progress checklist**

| Id | Stage | Status |
| --- | --- | --- |
| R1 | Requirement analysis (Core / DB / FR / NFR / Out of scope) | done |
| R2 | Skeleton (package, provider, layers, Deptrac, empty tests) | open |
| R2b | Module docs (`docs/` folder-structure, database, api) | open |
| R3 | Schema wall test passes against real Postgres | open |
| R4 | Features F1–F15 complete | open |
| R5 | Architectural boundaries A1–A5 verified | open |

Module documentation: `modules/product/docs/features-list.txt`,
`modules/product/docs/schema-design.txt`
(README, folder-structure, database, api still open until R2b).

Source of truth for scope: `doc/Features-list.txt` (if this section and that
file disagree, **Features-list wins**).

**Responsibility:** Owns sellable catalog data — products, variants,
categories, attributes, media, merchandising links. Nothing else.

**Public surface**
- REST API front door — format/transport only; no business rules in controllers
- `ProductApi` contract for other modules (DTOs only, never Eloquent) —
  e.g. `isActive(string $id): bool`
- Events: `ProductUpdated` written to `platform.outbox` in the same DB
  transaction as the state change

**Initial tables (schema `product`)** — sketch in
`modules/product/docs/schema-design.txt`. SQL later under
`Infrastructure/Persistence/`. Platform: `packages/platform/.../Bootstrap/`.
- `products` — listing (status, visibility, type, slug)
- `product_variants` — sellable SKU (every simple product has one)
- `categories`, `product_categories`
- `attributes` + options / values
- `product_media` (replaces the old `product_images` name in the full sketch)

**Architectural boundaries (must hold)**

| Id | Requirement |
| --- | --- |
| A1 | REST API front door — format/transport only; no business rules | open |
| A2 | Schema sealed (`product.*`) — no cross-schema FKs or joins in | open |
| A3 | State changes emit `ProductUpdated` to `platform.outbox`, same txn | open |
| A4 | Storefront reads via optimized batch queries (under 100ms target) | open |
| A5 | `ProductApi` contract; DTOs only | open |

**Features** (full text: `modules/product/docs/features-list.txt`)

| Id | Feature |
| --- | --- |
| F1 | Lifecycle Control — create, update, draft, publish, archive |
| F2 | Identifiers & Details — title, short/long description, brand; SKU, barcode, GTIN, UPC, EAN, ISBN, MPN |
| F3 | Pricing — base, compare-at/sale, sale start/end, cost, tax status/class |
| F4 | Types — physical, virtual, downloadable, grouped, bundles/kits, affiliate |
| F5 | Multi-option variants — SKU, barcode, price per combo, default variant |
| F6 | Attributes — reusable specs, swatches, facet flags |
| F7 | Media — gallery, thumbnail, reorder, variant map, downloads (limit/expiry) |
| F8 | Categorization — nested trees, tags, manual or automated collections |
| F9 | SEO — slugs, meta title/description, slug redirects |
| F10 | Relationships — related, up-sell, cross-sell, frequently bought together |
| F11 | Operations — admin list, duplicate, bulk edit, CSV import/export |
| F12 | Catalog visibility — visible / catalog / search / hidden |
| F13 | Featured flag |
| F14 | Shipping catalog data — weight, dimensions, shipping class (no quotes) |
| F15 | Sold individually — one per order (catalog flag) |

**Out of scope for the Product module** (do not implement here)

| Belongs in | Do not put in Product |
| --- | --- |
| Inventory | Stock, warehouses, reservations, backorders |
| Promotions | Coupons, campaigns, B2B price lists, bundle deals |
| Orders | Cart, checkout, payments, tax rates, download entitlement |
| Customer Reviews | Stars, reviews, Q&A |

### Backlog modules (named only — no feature lists yet)

Inventory · Promotions · Orders · Customer Reviews

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
  Scope is only what appears in `doc/Features-list.txt` (mirrored in this file).
- When scope changes: update `doc/Features-list.txt` **and** `AGENTS.md` together.
- When any module changes (code, schema, routes, contracts, features):
  update that module's `modules/<name>/docs/` in the **same turn**
  (README, folder-structure, database, api as applicable). Stale docs = not done.
- One active module at a time; one feature / use case per turn.
- Session notes go in `agent-log.txt`, not `readme.txt`. `readme.txt` is
  the developer's notebook; append answers there only when they ask.
