<?php

namespace Modules\Product\Api\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Product\Domain\Product;

/**
 * JSON shape the admin React SPA reads. Format only — no rules.
 *
 * { "data": { "id", "title", "short_description", "description", "brand",
 *   "sku", "barcode", "gtin", "mpn", "status", "visibility", "featured" } }
 */
final class ProductResource extends JsonResource
{
    public static $wrap = 'data';

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Product $product */
        $product = $this->resource;
        $variant = $product->defaultVariant();

        return [
            'id' => $product->id(),
            'title' => $product->title(),
            'short_description' => $product->shortDescription(),
            'description' => $product->description(),
            'brand' => $product->brand(),
            'sku' => $variant->sku(),
            'barcode' => $variant->barcode(),
            'gtin' => $variant->gtin(),
            'mpn' => $variant->mpn(),
            'price_minor' => $variant->priceMinor(),
            'compare_at_minor' => $variant->compareAtMinor(),
            'cost_minor' => $variant->costMinor(),
            'currency' => $variant->currency(),
            'sale_starts_at' => $variant->saleStartsAt()?->format(DATE_ATOM),
            'sale_ends_at' => $variant->saleEndsAt()?->format(DATE_ATOM),
            'tax_status' => $product->taxStatus()->value,
            'tax_class' => $product->taxClass(),
            'type' => $product->type()->value,
            'sold_individually' => $product->isSoldIndividually(),
            'external_url' => $product->externalUrl(),
            'slug' => $product->slug(),
            'meta_title' => $product->metaTitle(),
            'meta_description' => $product->metaDescription(),
            'status' => strtolower($product->status()->name),
            'visibility' => strtolower($product->visibility()->name),
            'featured' => $product->isFeatured(),
            'weight_g' => $product->weightG(),
            'length_mm' => $product->lengthMm(),
            'width_mm' => $product->widthMm(),
            'height_mm' => $product->heightMm(),
            'shipping_class' => $product->shippingClass(),
            'variant_weight_g' => $variant->weightG(),
            'variant_length_mm' => $variant->lengthMm(),
            'variant_width_mm' => $variant->widthMm(),
            'variant_height_mm' => $variant->heightMm(),
        ];
    }
}
