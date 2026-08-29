# Product — database schema and relations

**PostgreSQL schema:** `product`  
**Bootstrap:** `modules/product/database/bootstrap/schema.sql`  
**Runtime role:** `product_app` (DML only) · **DDL role:** `product_owner`  
**Connection:** `product` in `config/database.php` (`search_path = product`)

No foreign keys and no joins to other schemas. Cross-module links (if any later) are bare ids + comment, resolved via contracts.

---

## Tables (initial — Features-list)

| Table | Purpose | Key columns |
| --- | --- | --- |
| `products` | Product aggregate | `id` UUID, `title`, `slug`, `status`, `description`, `price_minor`, `currency`, `created_at` |
| `product_variants` | Sellable units | `id`, `product_id`, `sku`, `price`, `compare_at` |
| `categories` | Hierarchy | `id`, `name`, `parent_id`, `slug` |
| `attributes` | Spec type definitions | `id`, `name`, `type` |
| `product_images` | Gallery | `id`, `product_id`, `image_url`, `is_thumbnail` |

Further tables (category pivot, attribute values, tags, relations) are **additive** when F5–F10 are implemented. Update this file when migrations land.

---

## Relations (inside schema `product` only)

```
categories (self)
  parent_id ──► categories.id          optional tree edge

products
  │
  ├──◄── product_variants.product_id   1:N  variants
  ├──◄── product_images.product_id     1:N  images
  └── (later) category / tag / attribute / related-product links
```

| From | To | Cardinality | Constraint |
| --- | --- | --- | --- |
| `product_variants.product_id` | `products.id` | N:1 | FK inside `product` |
| `product_images.product_id` | `products.id` | N:1 | FK inside `product` |
| `categories.parent_id` | `categories.id` | N:1 | FK inside `product` (nullable root) |

Money columns use integer minor units + currency (architecture standard) when pricing columns are added (F2/F4). Ids are UUIDv7. Times are `TIMESTAMPTZ` UTC.

---

## Status values (domain)

`products.status`: `draft` | `active` | `archived`  
Only `active` is storefront-visible (F1).

---

## Migrations / seeders / factories

| Kind | Path |
| --- | --- |
| Migrations | `src/Infrastructure/Persistence/Migrations/` |
| Seeders | `src/Infrastructure/Persistence/Seeders/` |
| Factories | `src/Infrastructure/Persistence/Factories/` |

*(Empty until the first feature slice creates them.)*
