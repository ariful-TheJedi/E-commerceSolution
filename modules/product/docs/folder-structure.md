# Product — detailed folder structure

Complete layout of `modules/product`. Empty directories are intentional until a feature slice fills them.

```
modules/product/
│
├── composer.json
│     Package name: modules/product
│     PSR-4: Modules\Product\ → src/
│     Auto-discovers ProductServiceProvider
│
├── database/                          MODULE-OWNED DB BOOTSTRAP (not Laravel migrations)
│   └── bootstrap/
│       └── schema.sql                 CREATE SCHEMA product;
│                                      roles product_owner (DDL) + product_app (DML);
│                                      grants; REVOKE on platform/reporting/public
│
├── docs/                              MODULE DOCUMENTATION (keep in sync with code)
│   ├── README.md                      Overall brief + index
│   ├── folder-structure.md            This file
│   ├── database.md                    Tables, columns, relations
│   └── api.md                         HTTP routes, ProductApi, events
│
├── src/
│   │
│   ├── ProductServiceProvider.php     Registers migrations + routes with the host
│   │
│   ├── Contracts/                     ★ PUBLIC — the ONLY folder other modules may use
│   │   ├── ProductApi.php             isActive(), listActiveSummaries()
│   │   ├── Dto/
│   │   │   └── ProductSummaryDto.php
│   │   └── Events/                    Past-tense facts (e.g. ProductUpdated)
│   │       └── .gitkeep
│   │
│   ├── Api/                           ★ PRIVATE — HTTP edge only; no business rules
│   │   ├── Routes/
│   │   │   └── api.php                GET /api/v1/products (dummy)
│   │   ├── Controllers/
│   │   │   └── ListProductsController.php
│   │   ├── Requests/                  FormRequests — edge shape only (rules also in Domain)
│   │   │   └── .gitkeep
│   │   └── Resources/                 JSON response shaping
│   │       └── .gitkeep
│   │
│   ├── Application/                   ★ PRIVATE — orchestration per use case
│   │   ├── Ports/                     Interfaces this module needs (repos, outbox, …)
│   │   │   └── .gitkeep
│   │   ├── Listeners/                 Reactions to other modules' events (idempotent)
│   │   │   └── .gitkeep
│   │   └── <UseCase>/                 ONE FOLDER PER USE CASE (e.g. CreateProduct/)
│   │         ├── *Command.php
│   │         ├── *Handler.php
│   │         └── *Result.php
│   │
│   ├── Domain/                        ★ PRIVATE — pure PHP business rules
│   │   ├── .gitkeep                   (entities, value objects, rules land here)
│   │   ├── ValueObjects/              (when needed)
│   │   └── Rules/                     (when needed)
│   │       NO Illuminate, facades, Eloquent, Carbon, or now()
│   │
│   └── Infrastructure/                ★ PRIVATE — adapters + persistence
│       ├── ProductApiImpl.php         implements ProductApi (in-memory dummy)
│       ├── Adapters/                  External systems behind Application Ports
│       │   └── .gitkeep
│       ├── ReadModels/                Projections this module maintains
│       │   └── .gitkeep
│       └── Persistence/               ALL table DB code for schema product
│           ├── Models/
│           │   └── Product.php        Eloquent (Persistence only)
│           ├── Repositories/          Map models ↔ domain objects
│           │   └── .gitkeep
│           ├── Migrations/            Table DDL (loaded by ProductServiceProvider)
│           │   └── .gitkeep
│           ├── Seeders/
│           │   └── ProductDemoSeeder.php
│           └── Factories/
│               └── ProductFactory.php
│
└── tests/
    ├── Unit/                          Domain / pure logic (no DB)
    │   └── .gitkeep
    └── Integration/                   Real Postgres, this module's connection
        └── SchemaWallTest.php         product_app must not read other schemas
```

---

## What each area is for

| Path | Responsibility | May depend on |
| --- | --- | --- |
| `Contracts/` | Promise to the rest of the system | Shared kernel only |
| `Domain/` | Invariants and decisions | Nothing (pure PHP) |
| `Application/` | One use-case folder; calls Domain + Ports | Domain, Contracts, Shared |
| `Infrastructure/` | Eloquent, migrations, adapters, `ProductApiImpl` | Domain, Application, Platform, Laravel |
| `Api/` | HTTP in/out | Application, Contracts |
| `database/bootstrap/` | Schema + roles + grants SQL | — (run against Postgres) |
| `docs/` | Human-facing module docs | — |
| `tests/` | Unit + integration | — |

---

## Dependency direction (inward)

```
Api ──────────► Application ──────────► Domain
                      │                    ▲
                      ▼                    │
               Ports (interfaces)          │
                      ▲                    │
                      │                    │
               Infrastructure ─────────────┘
                      │
                      ├── implements ProductApi (Contracts)
                      └── Eloquent only under Persistence/Models
```

---

## Hard rules for this module

1. **One owner** — only Product writes/migrates `product.*`.
2. **Published surface** — outsiders use `Contracts/` or HTTP; never Models, tables, or internal classes.
3. **No UI here** — Blade/React live under host `frontend/`.
4. **No host migrations** — host `database/migrations/` stays empty.
5. **Validate twice** — FormRequest for HTTP shape; Domain for real rules (contracts skip FormRequest).
6. When you add a table, route, or contract method — update `docs/database.md` / `docs/api.md` in the same turn.
