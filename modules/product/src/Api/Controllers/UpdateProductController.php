<?php

namespace Modules\Product\Api\Controllers;

use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\UpdateProductRequest;
use Modules\Product\Api\Resources\ProductResource;
use Modules\Product\Application\Ports\ProductRepository;
use Modules\Product\Application\ProductVisibilityMap;
use Modules\Product\Application\UpdateProduct\UpdateProductCommand;
use Modules\Product\Application\UpdateProduct\UpdateProductHandler;
use Modules\Product\Domain\ProductTaxStatus;
use Modules\Product\Domain\ProductType;

/**
 * PATCH /api/v1/products/{id} — listing flags, copy, brand, SKU codes.
 * Bind the id, not a model.
 */
final class UpdateProductController
{
    public function __construct(
        private UpdateProductHandler $handler,
        private ProductRepository $products,
    ) {
    }

    public function __invoke(UpdateProductRequest $request, string $id): JsonResponse
    {
        $this->handler->handle(new UpdateProductCommand(
            id: $id,
            visibility: $request->has('visibility')
                ? ProductVisibilityMap::fromString($request->string('visibility')->toString())
                : null,
            featured: $request->exists('featured') ? $request->boolean('featured') : null,
            title: $request->exists('title') ? $request->string('title')->toString() : null,
            shortDescription: $request->exists('short_description') ? $request->string('short_description')->toString() : null,
            description: $request->exists('description') ? $request->string('description')->toString() : null,
            brand: $request->exists('brand') ? $request->string('brand')->toString() : null,
            sku: $request->exists('sku') ? $request->string('sku')->toString() : null,
            barcode: $request->exists('barcode') ? $request->string('barcode')->toString() : null,
            gtin: $request->exists('gtin') ? $request->string('gtin')->toString() : null,
            mpn: $request->exists('mpn') ? $request->string('mpn')->toString() : null,
            priceMinor: $request->exists('price_minor') ? $request->integer('price_minor') : null,
            compareAtMinor: $request->exists('compare_at_minor') ? $request->input('compare_at_minor') : null,
            costMinor: $request->exists('cost_minor') ? $request->input('cost_minor') : null,
            saleStartsAt: $request->filled('sale_starts_at') ? new DateTimeImmutable($request->string('sale_starts_at')->toString()) : null,
            saleEndsAt: $request->filled('sale_ends_at') ? new DateTimeImmutable($request->string('sale_ends_at')->toString()) : null,
            taxStatus: $request->exists('tax_status') ? ProductTaxStatus::from($request->string('tax_status')->toString()) : null,
            taxClass: $request->exists('tax_class') ? $request->string('tax_class')->toString() : null,
            type: $request->exists('type') ? ProductType::from($request->string('type')->toString()) : null,
            soldIndividually: $request->exists('sold_individually') ? $request->boolean('sold_individually') : null,
            externalUrl: $request->exists('external_url') ? $request->string('external_url')->toString() : null,
            slug: $request->exists('slug') ? $request->string('slug')->toString() : null,
            metaTitle: $request->exists('meta_title') ? $request->string('meta_title')->toString() : null,
            metaDescription: $request->exists('meta_description') ? $request->string('meta_description')->toString() : null,
            weightG: $request->exists('weight_g') ? $request->input('weight_g') : null,
            lengthMm: $request->exists('length_mm') ? $request->input('length_mm') : null,
            widthMm: $request->exists('width_mm') ? $request->input('width_mm') : null,
            heightMm: $request->exists('height_mm') ? $request->input('height_mm') : null,
            variantWeightG: $request->exists('variant_weight_g') ? $request->input('variant_weight_g') : null,
            variantLengthMm: $request->exists('variant_length_mm') ? $request->input('variant_length_mm') : null,
            variantWidthMm: $request->exists('variant_width_mm') ? $request->input('variant_width_mm') : null,
            variantHeightMm: $request->exists('variant_height_mm') ? $request->input('variant_height_mm') : null,
            shippingClass: $request->exists('shipping_class') ? $request->input('shipping_class') : null,
        ));

        return (new ProductResource($this->products->find($id)))
            ->response();
    }
}
