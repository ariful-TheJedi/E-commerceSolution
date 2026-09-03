<?php

namespace Modules\Product\Infrastructure\Persistence\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Product\Application\Ports\VariantRepository;
use Modules\Product\Domain\ProductOption;
use Modules\Product\Domain\AttributeOption;
use Modules\Product\Domain\ProductVariant;
use Modules\Product\Domain\VariantOptionSelection;

/** Maps feature-8 option dimensions and variant combinations to Product tables. */
final class EloquentVariantRepository implements VariantRepository
{
    public function createOption(ProductOption $option): void
    {
        DB::connection('product')->table('product_options')->insert(['id' => $option->id, 'product_id' => $option->productId, 'name' => $option->name, 'position' => $option->position, 'created_at' => now(), 'updated_at' => now()]);
    }
    public function optionValueBelongsToProduct(string $optionValueId, string $productId): bool
    {
        return DB::connection('product')->table('product_option_values as values')->join('product_options as options', 'options.id', '=', 'values.option_id')->where('values.id', $optionValueId)->where('options.product_id', $productId)->exists();
    }

    public function createOptionValue(AttributeOption $option): void
    {
        DB::connection('product')->table('product_option_values')->insert([
            'id' => $option->id, 'option_id' => $option->attributeId, 'label' => $option->label,
            'slug' => $option->slug, 'position' => $option->position, 'color_hex' => $option->colorHex,
            'image_path' => $option->imagePath, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
    public function optionValueOptionId(string $optionValueId): ?string
    {
        return DB::connection('product')->table('product_option_values')->where('id', $optionValueId)->value('option_id');
    }
    public function createVariant(ProductVariant $variant, string $productId, bool $isDefault, array $selections): void
    {
        DB::connection('product')->transaction(function () use ($variant, $productId, $isDefault, $selections): void {
            if ($isDefault) DB::connection('product')->table('product_variants')->where('product_id', $productId)->update(['is_default' => false]);
            DB::connection('product')->table('product_variants')->insert(['id' => $variant->id(), 'product_id' => $productId, 'sku' => $variant->sku(), 'barcode' => $variant->barcode(), 'gtin' => $variant->gtin(), 'mpn' => $variant->mpn(), 'is_default' => $isDefault, 'price_minor' => $variant->priceMinor(), 'currency' => $variant->currency(), 'created_at' => now(), 'updated_at' => now()]);
            foreach ($selections as $selection) DB::connection('product')->table('variant_option_values')->insert(['variant_id' => $selection->variantId, 'option_value_id' => $selection->optionValueId, 'option_id' => $selection->optionId]);
            if ($isDefault) DB::connection('product')->table('products')->where('id', $productId)->update(['default_variant_id' => $variant->id()]);
        });
    }
}
