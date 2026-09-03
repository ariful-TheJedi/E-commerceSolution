# Product module — public surface

How an **external system** or **another module** talks to Product.
Internals (`Domain`, `Application`, `Infrastructure`, tables in schema
`product`) are private. Do not call them.

Two doors:

  HTTP        another process / admin UI     →  part A
  Contract    another module in this app     →  part B


===============================================================================
A. HTTP API
===============================================================================

Base:           /api/v1
Module name:    never in the URL
In:             application/json
Out (ok):       application/json
Out (invalid):  application/problem+json
Auth:           none yet (Identity not built). Do not treat that as final.
GET catalog:    not published. No list/show/delete in this slice.


-------------------------------------------------------------------------------
A1. Words (JSON strings, lowercase)
-------------------------------------------------------------------------------

status        draft | active | archived
              draft = not for sale
              active = published
              archived = history only; listing flags, copy, and SKU codes frozen

visibility    visible | catalog | search | hidden
              where the listing may appear. Not featured.

featured      boolean. Home / merchandising. Independent of visibility.

id            UUIDv7 string. Server assigns on create. Client puts it in the path.

sku           required on create. Unique across this module’s variants.
              Not a stock quantity. This slice has one default SKU per listing
              (not a variants[] array — that is a later feature).

gtin          one stored code. UPC / EAN / ISBN are not separate fields.
barcode       optional. This module does not generate barcodes.
mpn           optional manufacturer part number.

brand         optional text on the listing. Not a brands catalog this slice.

Pricing       variant prices use integer minor units and a three-letter
              currency code. compare_at_minor and cost_minor are optional.
              Sale windows use UTC timestamps and require both boundaries.
Tax           product tax_status is taxable or none; tax_class is an optional
              code only. Tax rates and checkout calculations belong elsewhere.
Type           product type is physical, virtual, downloadable, grouped,
               bundle, or external. sold_individually is a catalog flag;
               external products may store an external URL.
SEO            product slugs are unique and title-derived when omitted.
               Slug changes write old and new product paths to url_redirects;
               category and collection slugs belong to their later features.


-------------------------------------------------------------------------------
A2. Endpoints
-------------------------------------------------------------------------------

Header on mutating POST:

    Idempotency-Key: <client-unique-string>

Accepted today. Replay store is not built yet. Send it anyway.

POST   /api/v1/products
       Create. Always starts draft. Defaults: visible, featured=false.
       Required: title, sku. Creates one default variant with the listing.
       201 Created
       Location: /api/v1/products/{id}

PATCH  /api/v1/products/{id}
      Any of: title, short_description, description, brand, sku, barcode,
      gtin, mpn, price_minor, compare_at_minor, cost_minor, currency,
      sale_starts_at, sale_ends_at, tax_status, tax_class, type,
      sold_individually, external_url, visibility, featured, weight_g,
      length_mm, width_mm, height_mm, variant_* shipping overrides,
      shipping_class.
      Omitted fields unchanged. Status
       unchanged. Empty string on an optional field clears it.
       200 OK
       Archived listings cannot be patched (domain, not 422).

POST   /api/v1/products/{id}/publish     draft → active. 200
POST   /api/v1/products/{id}/archive     draft or active → archived. 200
POST   /api/v1/products/{id}/draft       active or archived → draft. 200

{id} is the listing UUID. Not a bound Eloquent model.


-------------------------------------------------------------------------------
A3. Request bodies
-------------------------------------------------------------------------------

POST /api/v1/products

    {
      "title": "Wool coat",
      "sku": "WOOL-COAT",
      "short_description": "Warm wool.",
      "description": "A long coat for winter.",
      "brand": "North Mill",
      "barcode": "0123456789012",
      "gtin": "0123456789012",
      "mpn": "NM-WOOL-1",
      "visibility": "visible",
      "featured": false,
      "price_minor": 12999,
      "compare_at_minor": 15999,
      "cost_minor": 5000,
      "currency": "USD",
      "sale_starts_at": "2026-10-01T00:00:00+00:00",
      "sale_ends_at": "2026-10-31T23:59:59+00:00",
      "tax_status": "taxable",
      "tax_class": "standard",
      "weight_g": 1800,
      "length_mm": 800,
      "width_mm": 600,
      "height_mm": 120,
      "variant_weight_g": 1900,
      "shipping_class": "bulky"
    }

    title              string, required
    sku                string, required, unique
    short_description  optional string
    description        optional string (long copy)
    brand              optional string
    barcode, gtin, mpn optional strings
    visibility         optional, one of the four words (default visible)
    featured           optional boolean (default false)
    price_minor        optional non-negative integer minor units
    compare_at_minor   optional non-negative integer
    cost_minor         optional non-negative integer
    currency           optional uppercase three-letter code (default XXX)
    sale_starts_at/end optional UTC dates; both are required together
    tax_status         optional taxable or none (default taxable)
    tax_class          optional code; tax rates are outside Product
    weight_g           optional non-negative product default in grams
    length_mm,         optional non-negative product dimensions in millimetres
    width_mm, height_mm
    variant_weight_g,  optional default-variant overrides for each measurement
    variant_length_mm,
    variant_width_mm, variant_height_mm
    shipping_class     optional label only; no rates or carrier data
    type               optional physical, virtual, downloadable, grouped,
               bundle, or external
    sold_individually  optional boolean (default false)
    external_url       optional URL for external products
    slug               optional unique URL slug, derived from title when omitted
    meta_title         optional SEO title
    meta_description   optional SEO description

PATCH /api/v1/products/{id}

    {
      "title": "Wool coat updated",
      "sku": "WOOL-COAT-2",
      "visibility": "search",
      "featured": true
    }

    Send only fields you change.

POST publish / archive / draft:  {}

POST /api/v1/attributes
  Create a reusable definition with name, slug, data_type, filterable,
  sortable, and visible_on_pdp. data_type is text, number, boolean, or enum.

POST /api/v1/attributes/{id}/options
  Add an enum option with label, slug, position, and optional color_hex or
  image_path swatch metadata.

PUT /api/v1/products/{id}/specifications
  Assign attribute_id with either value or attribute_option_id. variant_id
  is optional. Enum attributes require an option; other types require value.
  This assigns a reusable specification and never creates a variant option.

POST /api/v1/products/{id}/media
  Add image metadata: path (required), optional alt, position, is_primary,
  and variant_id. The path points to the platform media disk.

PATCH /api/v1/products/{id}/media/{mediaId}
  Reorder or update alt text, primary state, and optional variant mapping.
  Moving an image shifts neighboring positions and primary is unique.

POST /api/v1/products/{id}/digital-files
  Add a file path with optional variant_id, download_limit, and
  expires_after_days. Orders grants access after purchase; Product does not.

POST /api/v1/categories and POST /api/v1/tags
  Create nested category or flat tag metadata. Categories accept an optional
  existing parent_id and position.

POST /api/v1/collections
  Create a manual or automatic collection with kind and optional all/any match.

PUT /api/v1/products/{id}/categories/{categoryId}
  Assign a category with optional canonical and position fields. Only one
  category is canonical for each product.

PUT /api/v1/products/{id}/tags/{tagId}
  Assign a flat tag to a product; repeating the assignment is idempotent.

PUT /api/v1/collections/{id}/products/{productId}
  Add or reposition a product in a collection.

POST /api/v1/collections/{id}/rules
  Add an automatic catalog rule using type, tag, brand, or attribute with
  eq, neq, or in. Rule evaluation is outside this write API.

PUT /api/v1/products/{id}/relationships/{toProductId}
  Create a manual relationship with kind related, upsell, cross_sell,
  alternative, or fbt. Both products must exist and self-links are rejected.
  Repeating the same link is harmless. Product does not generate links from
  orders and does not apply discounts.

GET /api/v1/admin/products?cursor=...&limit=...
  Return a cursor-paginated admin list, capped at 100 products per page.

POST /api/v1/admin/products/{id}/duplicate
  Duplicate a listing as a draft. The request supplies a unique sku and slug;
  the new product and default variant receive fresh ids.

PATCH /api/v1/admin/products/bulk
  Apply visibility and/or featured to an ids array. This does not edit stock,
  orders, or pricing.

POST /api/v1/admin/products/import
  Import CSV text with title, sku, and optional slug columns.

GET /api/v1/admin/products/export
  Download CSV containing product id, title, SKU, slug, status, visibility,
  and featured state.


-------------------------------------------------------------------------------
A4. Success body (every write)
-------------------------------------------------------------------------------

    {
      "data": {
        "id": "01900000-0000-7000-8000-000000000001",
        "title": "Wool coat",
        "short_description": null,
        "description": null,
        "brand": null,
        "sku": "WOOL-COAT",
        "barcode": null,
        "gtin": null,
        "mpn": null,
        "status": "draft",
        "visibility": "visible",
        "featured": false
      }
    }

Read `data`. No floats. No ORM objects. No variants array.


-------------------------------------------------------------------------------
A5. Errors
-------------------------------------------------------------------------------

Switch on `type`, never on the message text.

422 validation

    Content-Type: application/problem+json

    {
      "type": "https://ecommercesolution.test/problems/validation",
      "title": "The request is not valid.",
      "status": 422,
      "errors": {
        "title": ["The title field is required."]
      }
    }

Illegal lifecycle (publish when not draft, update when archived) and
duplicate SKU: no stable `type` URI yet. Do not parse 500 bodies.


-------------------------------------------------------------------------------
A6. Example
-------------------------------------------------------------------------------

    POST /api/v1/products
    Idempotency-Key: 0190-client-key-1
    Content-Type: application/json

    {"title":"Wool coat","sku":"WOOL-COAT"}

    POST /api/v1/products/{id}/publish
    Idempotency-Key: 0190-client-key-2


===============================================================================
B. CONTRACT  (other modules in this app)
===============================================================================

PHP:     Modules\Product\Contracts\
Folder:  modules/product/src/Contracts/

Allowed: this namespace only.

Not allowed:

  - Domain / Application / Api / Infrastructure classes
  - SQL or Eloquent on schema product
  - FK to product.products
  - HTTP from a storefront Blade controller (Blade uses the contract;
    React uses HTTP, part A)

Need the answer now to finish this request?  →  ProductApi (sync, batched).
Need to know something happened?            →  event (past tense, idempotent).


-------------------------------------------------------------------------------
B1. ProductApi  (questions)
-------------------------------------------------------------------------------

Interface:  Modules\Product\Contracts\ProductApi
Bound as:   QueryProductApi (repository, not Eloquent)

    isActive(string $id): bool
        true only when the listing exists and status is active (published).
        Draft, archived, and unknown ids are false. Not stock.

    areActive(list $ids): array<string, bool>
        Same question, many ids, one catalog read. Call this from a loop
        in another module — never isActive inside a loop.

Never Eloquent. DTOs later if a method returns a listing. Bool is enough
for “is this sellable?” at catalog level.


-------------------------------------------------------------------------------
B2. ProductUpdated  (fact)
-------------------------------------------------------------------------------

Class:  Modules\Product\Contracts\Events\ProductUpdated

When: create, update flags / copy / SKU codes, publish, archive, draft.

    eventId      string UUIDv7     this fact; handlers key on it
    productId    string UUIDv7     listing that changed
    occurredAt   DateTimeImmutable UTC

No title, sku, or status on the event. Need current state later → ProductApi.
Need a historical copy → write it in YOUR table when you handle the event.

Delivery: platform.outbox, same database transaction as the listing row.
This module’s adapter inserts the row (no FK). Platform owns the table.
The worker that drains the bus is not this slice.

    id            = eventId (unique; second insert with the same id is a no-op)
    type          = Modules\Product\Contracts\Events\ProductUpdated
    payload       = { "productId": "..." }
    occurred_at   UTC
    published_at  null until a worker marks it

Handler:

  - own transaction, own schema
  - same eventId twice → same outcome
  - never write product.*


-------------------------------------------------------------------------------
B3. Ids you store
-------------------------------------------------------------------------------

Need the live listing later: store our id (UUID), no FK, index it, resolve
through ProductApi.

Need the value at event time: copy it. Do not update the copy.
