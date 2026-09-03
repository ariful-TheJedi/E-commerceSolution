-- Product module migration 20260903_000001.
-- Run as product_owner. Additive only; safe to apply once to an existing schema.

ALTER TABLE product.products
    ADD COLUMN IF NOT EXISTS shipping_class TEXT NULL;

ALTER TABLE product.products
    ADD COLUMN IF NOT EXISTS brand TEXT NULL;

ALTER TABLE product.product_relations
    DROP CONSTRAINT IF EXISTS product_relations_kind_chk;

ALTER TABLE product.product_relations
    ADD CONSTRAINT product_relations_kind_chk
    CHECK (kind IN ('related', 'upsell', 'cross_sell', 'alternative', 'fbt'));
