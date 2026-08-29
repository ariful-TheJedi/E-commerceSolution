# Product module

## Overall brief

**Product** is the catalog module of the modular monolith. It owns everything about *what can be sold*: products, variants/SKUs, categories, attributes, media, pricing metadata, and SEO fields. It does **not** own stock, checkout, discounts, or reviews — those belong in other modules.

| | |
| --- | --- |
| **Folder** | `modules/product` |
| **Namespace** | `Modules\Product\` |
| **Postgres schema** | `product` (sealed — no cross-schema FKs/joins) |
| **Composer package** | `modules/product` |
| **Public surface** | `Contracts/` (`ProductApi` + DTOs + events) and HTTP `/api/v1/...` (module name never in URLs) |
| **Consumers** | Admin React SPA via HTTP; storefront Blade via in-process `ProductApi`; other modules via `ProductApi` only |

**How it fits the host:** one deployable app, one database, one schema per module. Product writes only `product.*`, announces facts through `platform.outbox` (`ProductUpdated` in the same transaction as the change), and answers questions for other modules with readonly DTOs — never Eloquent models.

**Capabilities (from Features-list):** lifecycle (draft / active / archived), pricing, physical vs digital types, multi-option variants, custom attributes, media gallery, category tree + tags, SEO, cross-sell links. Architectural must-holds: thin REST front door, sealed schema, outbox on state change, batch storefront reads, `ProductApi` with DTOs only.

**Current stage:** skeleton + **dummy** `GET /api/v1/products` and storefront island.
Real tables / F1 not built. Authoritative progress: host `doc/Features-list.txt`.

**Out of scope:** Inventory (stock), Promotions/Orders (cart/checkout/payments), Customer Reviews.

---

## Documentation in this folder

| Doc | Contents |
| --- | --- |
| [folder-structure.md](folder-structure.md) | Detailed layout of every folder and what belongs there |
| [database.md](database.md) | Schema, tables, relations (inside `product` only) |
| [api.md](api.md) | HTTP routes, contracts, events |

Scope and feature checklist: `doc/Features-list.txt` · System shape: `doc/architecture-map.txt`.
