-- Product module. Schema product only.
-- Design: modules/product/docs/database-design.txt
-- Apply as a superuser on database eCommercesolution (after CREATE DATABASE).

CREATE SCHEMA IF NOT EXISTS product;

DO $roles$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'product_owner') THEN
        CREATE ROLE product_owner LOGIN PASSWORD 'secret';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'product_app') THEN
        CREATE ROLE product_app LOGIN PASSWORD 'secret';
    END IF;
END
$roles$;

GRANT USAGE, CREATE ON SCHEMA product TO product_owner;
GRANT USAGE ON SCHEMA product TO product_app;
ALTER ROLE product_owner SET search_path TO product;
ALTER ROLE product_app SET search_path TO product;

REVOKE ALL ON SCHEMA product FROM PUBLIC;

-------------------------------------------------------------------------------
-- Lookup tables
-------------------------------------------------------------------------------

CREATE TABLE product.brands (
    id          UUID PRIMARY KEY,
    name        TEXT NOT NULL,
    slug        TEXT NOT NULL UNIQUE,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at  TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE product.shipping_classes (
    id          UUID PRIMARY KEY,
    name        TEXT NOT NULL,
    slug        TEXT NOT NULL UNIQUE,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at  TIMESTAMPTZ NOT NULL DEFAULT now()
);

-------------------------------------------------------------------------------
-- Listing + SKU  (default_variant_id FK added after variants exist)
-------------------------------------------------------------------------------

CREATE TABLE product.products (
    id                    UUID PRIMARY KEY,
    brand_id              UUID NULL REFERENCES product.brands (id),
    shipping_class_id     UUID NULL REFERENCES product.shipping_classes (id),
    shipping_class        TEXT NULL,
    default_variant_id    UUID NULL,
    type                  TEXT NOT NULL,
    status                TEXT NOT NULL,
    visibility            TEXT NOT NULL,
    featured              BOOLEAN NOT NULL DEFAULT false,
    sold_individually     BOOLEAN NOT NULL DEFAULT false,
    title                 TEXT NOT NULL,
    slug                  TEXT NOT NULL UNIQUE,
    short_description     TEXT NULL,
    description           TEXT NULL,
    brand                 TEXT NULL,
    external_url          TEXT NULL,
    tax_status            TEXT NOT NULL,
    tax_class             TEXT NULL,
    weight_g              INTEGER NULL,
    length_mm             INTEGER NULL,
    width_mm              INTEGER NULL,
    height_mm             INTEGER NULL,
    meta_title            TEXT NULL,
    meta_description      TEXT NULL,
    search_indexable      BOOLEAN NOT NULL DEFAULT true,
    created_at            TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at            TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT products_type_chk CHECK (type IN (
        'physical', 'virtual', 'downloadable', 'grouped', 'bundle', 'external'
    )),
    CONSTRAINT products_status_chk CHECK (status IN ('draft', 'active', 'archived')),
    CONSTRAINT products_visibility_chk CHECK (visibility IN (
        'visible', 'catalog', 'search', 'hidden'
    )),
    CONSTRAINT products_tax_status_chk CHECK (tax_status IN ('taxable', 'none'))
);

CREATE INDEX products_status_visibility_idx ON product.products (status, visibility);
CREATE INDEX products_brand_id_idx ON product.products (brand_id);
CREATE INDEX products_slug_idx ON product.products (slug);

CREATE TABLE product.product_variants (
    id                 UUID PRIMARY KEY,
    product_id         UUID NOT NULL REFERENCES product.products (id) ON DELETE CASCADE,
    sku                TEXT NOT NULL UNIQUE,
    barcode            TEXT NULL,
    gtin               TEXT NULL,
    mpn                TEXT NULL,
    is_default         BOOLEAN NOT NULL DEFAULT false,
    price_minor        INTEGER NOT NULL,
    compare_at_minor   INTEGER NULL,
    cost_minor         INTEGER NULL,
    currency           CHAR(3) NOT NULL,
    sale_starts_at     TIMESTAMPTZ NULL,
    sale_ends_at       TIMESTAMPTZ NULL,
    weight_g           INTEGER NULL,
    length_mm          INTEGER NULL,
    width_mm           INTEGER NULL,
    height_mm          INTEGER NULL,
    created_at         TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at         TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX product_variants_product_id_idx ON product.product_variants (product_id);
CREATE UNIQUE INDEX product_variants_one_default_idx
    ON product.product_variants (product_id)
    WHERE is_default;

ALTER TABLE product.products
    ADD CONSTRAINT products_default_variant_fk
    FOREIGN KEY (default_variant_id)
    REFERENCES product.product_variants (id)
    ON DELETE SET NULL
    DEFERRABLE INITIALLY DEFERRED;

-------------------------------------------------------------------------------
-- Organization
-------------------------------------------------------------------------------

CREATE TABLE product.categories (
    id          UUID PRIMARY KEY,
    parent_id   UUID NULL REFERENCES product.categories (id),
    name        TEXT NOT NULL,
    slug        TEXT NOT NULL UNIQUE,
    position    INTEGER NOT NULL DEFAULT 0,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at  TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX categories_parent_id_idx ON product.categories (parent_id);

CREATE TABLE product.product_categories (
    product_id    UUID NOT NULL REFERENCES product.products (id) ON DELETE CASCADE,
    category_id   UUID NOT NULL REFERENCES product.categories (id) ON DELETE CASCADE,
    is_canonical  BOOLEAN NOT NULL DEFAULT false,
    position      INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (product_id, category_id)
);

CREATE UNIQUE INDEX product_categories_one_canonical_idx
    ON product.product_categories (product_id)
    WHERE is_canonical;

CREATE TABLE product.tags (
    id          UUID PRIMARY KEY,
    name        TEXT NOT NULL,
    slug        TEXT NOT NULL UNIQUE,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at  TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE product.product_tags (
    product_id  UUID NOT NULL REFERENCES product.products (id) ON DELETE CASCADE,
    tag_id      UUID NOT NULL REFERENCES product.tags (id) ON DELETE CASCADE,
    PRIMARY KEY (product_id, tag_id)
);

CREATE TABLE product.collections (
    id          UUID PRIMARY KEY,
    name        TEXT NOT NULL,
    slug        TEXT NOT NULL UNIQUE,
    kind        TEXT NOT NULL,
    match       TEXT NULL,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT collections_kind_chk CHECK (kind IN ('manual', 'automatic')),
    CONSTRAINT collections_match_chk CHECK (match IS NULL OR match IN ('all', 'any'))
);

CREATE TABLE product.collection_rules (
    id              UUID PRIMARY KEY,
    collection_id   UUID NOT NULL REFERENCES product.collections (id) ON DELETE CASCADE,
    field           TEXT NOT NULL,
    operator        TEXT NOT NULL,
    value           TEXT NOT NULL,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT collection_rules_field_chk CHECK (field IN ('type', 'tag', 'brand', 'attribute')),
    CONSTRAINT collection_rules_operator_chk CHECK (operator IN ('eq', 'neq', 'in'))
);

CREATE INDEX collection_rules_collection_id_idx ON product.collection_rules (collection_id);

CREATE TABLE product.collection_products (
    collection_id  UUID NOT NULL REFERENCES product.collections (id) ON DELETE CASCADE,
    product_id     UUID NOT NULL REFERENCES product.products (id) ON DELETE CASCADE,
    position       INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (collection_id, product_id)
);

-------------------------------------------------------------------------------
-- Options / variants
-------------------------------------------------------------------------------

CREATE TABLE product.product_options (
    id          UUID PRIMARY KEY,
    product_id  UUID NOT NULL REFERENCES product.products (id) ON DELETE CASCADE,
    name        TEXT NOT NULL,
    position    INTEGER NOT NULL DEFAULT 0,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE (product_id, name)
);

CREATE INDEX product_options_product_id_idx ON product.product_options (product_id);

CREATE TABLE product.product_option_values (
    id          UUID PRIMARY KEY,
    option_id   UUID NOT NULL REFERENCES product.product_options (id) ON DELETE CASCADE,
    label       TEXT NOT NULL,
    slug        TEXT NOT NULL,
    position    INTEGER NOT NULL DEFAULT 0,
    color_hex   TEXT NULL,
    image_path  TEXT NULL,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE (option_id, slug)
);

CREATE INDEX product_option_values_option_id_idx ON product.product_option_values (option_id);

CREATE TABLE product.variant_option_values (
    variant_id        UUID NOT NULL REFERENCES product.product_variants (id) ON DELETE CASCADE,
    option_value_id   UUID NOT NULL REFERENCES product.product_option_values (id) ON DELETE RESTRICT,
    option_id         UUID NOT NULL REFERENCES product.product_options (id) ON DELETE CASCADE,
    PRIMARY KEY (variant_id, option_value_id),
    UNIQUE (variant_id, option_id)
);

-------------------------------------------------------------------------------
-- Attributes
-------------------------------------------------------------------------------

CREATE TABLE product.attributes (
    id              UUID PRIMARY KEY,
    name            TEXT NOT NULL,
    slug            TEXT NOT NULL UNIQUE,
    data_type       TEXT NOT NULL,
    filterable      BOOLEAN NOT NULL DEFAULT false,
    sortable        BOOLEAN NOT NULL DEFAULT false,
    visible_on_pdp  BOOLEAN NOT NULL DEFAULT true,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT attributes_data_type_chk CHECK (data_type IN ('text', 'number', 'boolean', 'enum'))
);

CREATE TABLE product.attribute_options (
    id            UUID PRIMARY KEY,
    attribute_id  UUID NOT NULL REFERENCES product.attributes (id) ON DELETE CASCADE,
    label         TEXT NOT NULL,
    slug          TEXT NOT NULL,
    position      INTEGER NOT NULL DEFAULT 0,
    color_hex     TEXT NULL,
    image_path    TEXT NULL,
    created_at    TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at    TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE (attribute_id, slug)
);

CREATE TABLE product.product_attribute_values (
    id                    UUID PRIMARY KEY,
    product_id            UUID NOT NULL REFERENCES product.products (id) ON DELETE CASCADE,
    variant_id            UUID NULL REFERENCES product.product_variants (id) ON DELETE CASCADE,
    attribute_id          UUID NOT NULL REFERENCES product.attributes (id) ON DELETE CASCADE,
    value_text            TEXT NULL,
    attribute_option_id   UUID NULL REFERENCES product.attribute_options (id),
    created_at            TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at            TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX product_attribute_values_product_id_idx ON product.product_attribute_values (product_id);
CREATE INDEX product_attribute_values_variant_id_idx ON product.product_attribute_values (variant_id);
CREATE INDEX product_attribute_values_attribute_id_idx ON product.product_attribute_values (attribute_id);

-------------------------------------------------------------------------------
-- Grouped / bundle
-------------------------------------------------------------------------------

CREATE TABLE product.product_components (
    id                  UUID PRIMARY KEY,
    parent_product_id   UUID NOT NULL REFERENCES product.products (id) ON DELETE CASCADE,
    child_product_id    UUID NULL REFERENCES product.products (id),
    child_variant_id    UUID NULL REFERENCES product.product_variants (id),
    quantity            INTEGER NOT NULL,
    kind                TEXT NOT NULL,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT product_components_qty_chk CHECK (quantity >= 1),
    CONSTRAINT product_components_kind_chk CHECK (kind IN ('grouped', 'bundle')),
    CONSTRAINT product_components_child_chk CHECK (
        (child_product_id IS NOT NULL AND child_variant_id IS NULL)
        OR (child_product_id IS NULL AND child_variant_id IS NOT NULL)
    )
);

CREATE INDEX product_components_parent_idx ON product.product_components (parent_product_id);

-------------------------------------------------------------------------------
-- Media + digital files (paths only)
-------------------------------------------------------------------------------

CREATE TABLE product.product_media (
    id          UUID PRIMARY KEY,
    product_id  UUID NOT NULL REFERENCES product.products (id) ON DELETE CASCADE,
    variant_id  UUID NULL REFERENCES product.product_variants (id) ON DELETE SET NULL,
    kind        TEXT NOT NULL,
    path        TEXT NOT NULL,
    alt         TEXT NULL,
    position    INTEGER NOT NULL DEFAULT 0,
    is_primary  BOOLEAN NOT NULL DEFAULT false,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT product_media_kind_chk CHECK (kind IN ('image', 'video'))
);

CREATE INDEX product_media_product_position_idx ON product.product_media (product_id, position);
CREATE UNIQUE INDEX product_media_one_primary_idx
    ON product.product_media (product_id)
    WHERE is_primary;

CREATE TABLE product.digital_files (
    id                  UUID PRIMARY KEY,
    product_id          UUID NOT NULL REFERENCES product.products (id) ON DELETE CASCADE,
    variant_id          UUID NULL REFERENCES product.product_variants (id) ON DELETE SET NULL,
    path                TEXT NOT NULL,
    download_limit      INTEGER NULL,
    expires_after_days  INTEGER NULL,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX digital_files_product_id_idx ON product.digital_files (product_id);

-------------------------------------------------------------------------------
-- Relations + redirects
-------------------------------------------------------------------------------

CREATE TABLE product.product_relations (
    from_product_id  UUID NOT NULL REFERENCES product.products (id) ON DELETE CASCADE,
    to_product_id    UUID NOT NULL REFERENCES product.products (id) ON DELETE CASCADE,
    kind             TEXT NOT NULL,
    PRIMARY KEY (from_product_id, to_product_id, kind),
    CONSTRAINT product_relations_kind_chk CHECK (kind IN ('related', 'upsell', 'cross_sell', 'alternative', 'fbt')),
    CONSTRAINT product_relations_no_self_chk CHECK (from_product_id <> to_product_id)
);

CREATE TABLE product.url_redirects (
    id          UUID PRIMARY KEY,
    from_path   TEXT NOT NULL UNIQUE,
    to_path     TEXT NOT NULL,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now()
);

-------------------------------------------------------------------------------
-- Grants: owner DDL, app DML only, no other schemas
-------------------------------------------------------------------------------

GRANT ALL ON SCHEMA product TO product_owner;
GRANT ALL ON ALL TABLES IN SCHEMA product TO product_owner;
GRANT ALL ON ALL SEQUENCES IN SCHEMA product TO product_owner;
ALTER DEFAULT PRIVILEGES FOR ROLE product_owner IN SCHEMA product
    GRANT ALL ON TABLES TO product_owner;

GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA product TO product_app;
GRANT USAGE ON ALL SEQUENCES IN SCHEMA product TO product_app;
ALTER DEFAULT PRIVILEGES FOR ROLE product_owner IN SCHEMA product
    GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO product_app;
ALTER DEFAULT PRIVILEGES FOR ROLE product_owner IN SCHEMA product
    GRANT USAGE ON SEQUENCES TO product_app;

REVOKE CREATE ON SCHEMA product FROM product_app;

DO $connect$
BEGIN
    EXECUTE format(
        'GRANT CONNECT ON DATABASE %I TO product_owner, product_app',
        current_database()
    );
END
$connect$;

-- Adapter writes platform.outbox on this connection (same transaction).
-- Platform owns the table; this grant is the documented write exception.
GRANT USAGE ON SCHEMA platform TO product_app;
GRANT INSERT, SELECT ON platform.outbox TO product_app;
