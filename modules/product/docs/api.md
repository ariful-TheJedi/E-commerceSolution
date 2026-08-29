# Product — API routes and contracts

Module name **never** appears in URLs. JSON under `/api/v1`.  
Errors: RFC 9457 problem details. Mutating writes: `Idempotency-Key`. Lists: cursor pagination.

HTTP controllers are transport only — no business rules (A1). Rules live in Domain; orchestration in Application handlers.

---

## HTTP routes (live)

| Method | Path | Status | Purpose |
| --- | --- | --- | --- |
| `GET` | `/api/v1/products` | **live (dummy)** | List active product summaries via `ProductApi::listActiveSummaries()`. In-memory stub until F1 + DB. |

Response shape:

```json
{
  "data": [
    {
      "id": "…",
      "title": "…",
      "slug": "…",
      "status": "active",
      "description": "…",
      "price_minor": 4999,
      "currency": "USD"
    }
  ]
}
```

Demo data: `ProductFactory` via `ProductApiImpl` (`make()`, not persisted).  
Seeder (when DB exists): `Infrastructure/Persistence/Seeders/ProductDemoSeeder`.

Storefront: Blade on `welcome.blade.php` calls `ProductApi` in-process (visible HTML).  
Island `ProductDemoList.tsx` also fetches `/api/v1/products`.  
Admin: SPA shell `admin.blade.php` → React `/admin/products`.

---

## HTTP routes (planned)

Mark each **done** when the real slice ships; keep this file in sync.

### Products (F1, F2, F3, F9)

| Method | Path | Purpose |
| --- | --- | --- |
| `POST` | `/api/v1/products` | Create product (draft) |
| `GET` | `/api/v1/products` | List (cursor) — replace dummy |
| `GET` | `/api/v1/products/{id}` | Get one |
| `PATCH` | `/api/v1/products/{id}` | Update fields (incl. pricing/SEO/type) |
| `POST` | `/api/v1/products/{id}/publish` | → active |
| `POST` | `/api/v1/products/{id}/archive` | → archived |

### Variants (F4)

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/api/v1/products/{id}/variants` | List variants |
| `POST` | `/api/v1/products/{id}/variants` | Create variant |
| `PATCH` | `/api/v1/variants/{id}` | Update variant |
| `GET` | `/api/v1/variants/{id}` | Get variant |

### Categories (F7)

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/api/v1/categories` | Tree / list |
| `POST` | `/api/v1/categories` | Create |
| `PATCH` | `/api/v1/categories/{id}` | Update |
| `PUT` | `/api/v1/products/{id}/categories` | Assign categories |

### Attributes (F5)

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/api/v1/attributes` | List attribute definitions |
| `POST` | `/api/v1/attributes` | Create definition |
| *(assign)* | via product/variant update or dedicated assign route when F5 is sliced | |

### Media (F6)

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/api/v1/products/{id}/images` | Gallery |
| `POST` | `/api/v1/products/{id}/images` | Upload / add |
| `PATCH` | `/api/v1/products/{id}/images/{imageId}` | Thumbnail / reorder / variant map |
| `DELETE` | `/api/v1/products/{id}/images/{imageId}` | Remove |

### Tags & cross-sell (F8, F10)

Document concrete paths when those slices start; do not invent until Features-list is expanded.

---

## Published contract (in-process)

| Symbol | Role |
| --- | --- |
| `Modules\Product\Contracts\ProductApi` | Other modules query products here |
| `isActive(string $id): bool` | Active check (A5) — stubbed |
| `listActiveSummaries(): list<ProductSummaryDto>` | Active summaries — stubbed |
| DTOs | `Contracts/Dto/ProductSummaryDto` — readonly; never Eloquent |
| Implementation | `Infrastructure/ProductApiImpl.php` (**in-memory dummy** until F1) |

Storefront Blade uses this contract in-process. Admin React uses HTTP `/api/v1` only.

---

## Events (outbox)

| Event | When | Notes |
| --- | --- | --- |
| `ProductUpdated` | Product state changes | Written to `platform.outbox` in the **same** transaction as the write (A3). Past tense, `eventId`, idempotent consumers. |

Additional events only if added to Features-list first.

---

## Route file

`src/Api/Routes/api.php` — loaded by `ProductServiceProvider`. Currently an empty `/api/v1` group.
