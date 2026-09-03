<?php

namespace Modules\Product\Infrastructure\Persistence\Factories;

use Illuminate\Support\Facades\DB;
use Shared\Ids;

/** Seeds a complete, relational Product catalog for local and staging environments. */
final class ProductCatalogFactory
{
    public function __construct(private Ids $ids) {}

    private function id(): string
    {
        return $this->ids->uuid7();
    }

    public function seed(): void
    {
        $db = DB::connection('product');
        if ($db->table('products')->where('slug', 'wool-coat')->exists()) {
            return;
        }
        $db->transaction(function () use ($db): void {
            $now = now();
            $brand = $this->id();
            $shipping = $this->id();
            $db->table('brands')->insert(['id' => $brand, 'name' => 'North Mill', 'slug' => 'north-mill', 'created_at' => $now, 'updated_at' => $now]);
            $db->table('shipping_classes')->insert(['id' => $shipping, 'name' => 'Standard', 'slug' => 'standard', 'created_at' => $now, 'updated_at' => $now]);

            $products = [];
            $variants = [];
            foreach ([['Wool Coat', 'wool-coat', 'WOOL-COAT', 'active'], ['Canvas Sneaker', 'canvas-sneaker', 'CANVAS-SNEAKER', 'active'], ['Digital Field Guide', 'digital-field-guide', 'FIELD-GUIDE', 'draft']] as $index => [$title, $slug, $sku, $status]) {
                $product = $this->id(); $variant = $this->id();
                $products[] = ['id' => $product, 'brand_id' => $brand, 'shipping_class_id' => $shipping, 'shipping_class' => 'standard', 'default_variant_id' => null, 'type' => $index === 2 ? 'downloadable' : 'physical', 'status' => $status, 'visibility' => 'visible', 'featured' => $index === 0, 'sold_individually' => false, 'title' => $title, 'slug' => $slug, 'short_description' => 'Catalog sample product.', 'description' => 'Seeded Product module catalog record.', 'brand' => 'North Mill', 'external_url' => null, 'tax_status' => 'taxable', 'tax_class' => 'standard', 'weight_g' => $index === 2 ? null : 900, 'length_mm' => $index === 2 ? null : 300, 'width_mm' => $index === 2 ? null : 220, 'height_mm' => $index === 2 ? null : 80, 'meta_title' => $title, 'meta_description' => 'Seeded catalog product.', 'search_indexable' => true, 'created_at' => $now, 'updated_at' => $now];
                $variants[] = ['id' => $variant, 'product_id' => $product, 'sku' => $sku, 'barcode' => null, 'gtin' => null, 'mpn' => null, 'is_default' => true, 'price_minor' => 12999 + ($index * 5000), 'compare_at_minor' => null, 'cost_minor' => null, 'currency' => 'USD', 'sale_starts_at' => null, 'sale_ends_at' => null, 'weight_g' => null, 'length_mm' => null, 'width_mm' => null, 'height_mm' => null, 'created_at' => $now, 'updated_at' => $now];
            }
            $db->table('products')->insert($products); $db->table('product_variants')->insert($variants);
            foreach ($products as $index => $product) $db->table('products')->where('id', $product['id'])->update(['default_variant_id' => $variants[$index]['id']]);

            $root = $this->id(); $child = $this->id();
            $db->table('categories')->insert([['id' => $root, 'parent_id' => null, 'name' => 'Apparel', 'slug' => 'apparel', 'position' => 0, 'created_at' => $now, 'updated_at' => $now], ['id' => $child, 'parent_id' => $root, 'name' => 'Outerwear', 'slug' => 'outerwear', 'position' => 0, 'created_at' => $now, 'updated_at' => $now]]);
            $db->table('product_categories')->insert([['product_id' => $products[0]['id'], 'category_id' => $root, 'is_canonical' => false, 'position' => 1], ['product_id' => $products[0]['id'], 'category_id' => $child, 'is_canonical' => true, 'position' => 0], ['product_id' => $products[1]['id'], 'category_id' => $root, 'is_canonical' => true, 'position' => 0]]);
            $tag = $this->id(); $db->table('tags')->insert(['id' => $tag, 'name' => 'Winter', 'slug' => 'winter', 'created_at' => $now, 'updated_at' => $now]);
            $db->table('product_tags')->insert([['product_id' => $products[0]['id'], 'tag_id' => $tag], ['product_id' => $products[1]['id'], 'tag_id' => $tag]]);

            $manual = $this->id(); $automatic = $this->id();
            $db->table('collections')->insert([['id' => $manual, 'name' => 'Staff picks', 'slug' => 'staff-picks', 'kind' => 'manual', 'match' => null, 'created_at' => $now, 'updated_at' => $now], ['id' => $automatic, 'name' => 'Winter edit', 'slug' => 'winter-edit', 'kind' => 'automatic', 'match' => 'all', 'created_at' => $now, 'updated_at' => $now]]);
            $db->table('collection_products')->insert([['collection_id' => $manual, 'product_id' => $products[0]['id'], 'position' => 0], ['collection_id' => $manual, 'product_id' => $products[1]['id'], 'position' => 1]]);
            $db->table('collection_rules')->insert(['id' => $this->id(), 'collection_id' => $automatic, 'field' => 'tag', 'operator' => 'eq', 'value' => 'winter', 'created_at' => $now, 'updated_at' => $now]);

            $option = $this->id(); $optionValue = $this->id();
            $db->table('product_options')->insert(['id' => $option, 'product_id' => $products[0]['id'], 'name' => 'Size', 'position' => 0, 'created_at' => $now, 'updated_at' => $now]);
            $db->table('product_option_values')->insert(['id' => $optionValue, 'option_id' => $option, 'label' => 'Medium', 'slug' => 'medium', 'position' => 0, 'color_hex' => null, 'image_path' => null, 'created_at' => $now, 'updated_at' => $now]);
            $db->table('variant_option_values')->insert(['variant_id' => $variants[0]['id'], 'option_value_id' => $optionValue, 'option_id' => $option]);
            $attribute = $this->id(); $attributeOption = $this->id();
            $db->table('attributes')->insert(['id' => $attribute, 'name' => 'Material', 'slug' => 'material', 'data_type' => 'enum', 'filterable' => true, 'sortable' => false, 'visible_on_pdp' => true, 'created_at' => $now, 'updated_at' => $now]);
            $db->table('attribute_options')->insert(['id' => $attributeOption, 'attribute_id' => $attribute, 'label' => 'Wool', 'slug' => 'wool', 'position' => 0, 'color_hex' => null, 'image_path' => null, 'created_at' => $now, 'updated_at' => $now]);
            $db->table('product_attribute_values')->insert(['id' => $this->id(), 'product_id' => $products[0]['id'], 'variant_id' => null, 'attribute_id' => $attribute, 'value_text' => null, 'attribute_option_id' => $attributeOption, 'created_at' => $now, 'updated_at' => $now]);

            $component = $this->id(); $db->table('product_components')->insert(['id' => $component, 'parent_product_id' => $products[0]['id'], 'child_product_id' => $products[1]['id'], 'child_variant_id' => null, 'quantity' => 1, 'kind' => 'grouped', 'created_at' => $now, 'updated_at' => $now]);
            $db->table('product_media')->insert(['id' => $this->id(), 'product_id' => $products[0]['id'], 'variant_id' => null, 'kind' => 'image', 'path' => 'products/wool-coat/front.webp', 'alt' => 'Wool coat', 'position' => 0, 'is_primary' => true, 'created_at' => $now, 'updated_at' => $now]);
            $db->table('digital_files')->insert(['id' => $this->id(), 'product_id' => $products[2]['id'], 'variant_id' => $variants[2]['id'], 'path' => 'products/field-guide/guide.pdf', 'download_limit' => 5, 'expires_after_days' => 30, 'created_at' => $now, 'updated_at' => $now]);
            $db->table('product_relations')->insert([['from_product_id' => $products[0]['id'], 'to_product_id' => $products[1]['id'], 'kind' => 'related'], ['from_product_id' => $products[1]['id'], 'to_product_id' => $products[0]['id'], 'kind' => 'alternative']]);
            $db->table('url_redirects')->insert(['id' => $this->id(), 'from_path' => '/products/old-wool-coat', 'to_path' => '/products/wool-coat', 'created_at' => $now]);
        });
    }
}