<?php

namespace Modules\Product\Api\Controllers;

use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\CreateProductRequest;
use Modules\Product\Api\Resources\ProductResource;
use Modules\Product\Application\CreateProduct\CreateProductCommand;
use Modules\Product\Application\CreateProduct\CreateProductHandler;
use Modules\Product\Domain\ProductTaxStatus;
use Modules\Product\Domain\ProductType;
use Modules\Product\Application\Ports\ProductRepository;
use Modules\Product\Application\ProductVisibilityMap;
use Shared\Ids;

/**
 * POST /api/v1/products — create a draft listing with a default SKU. Thin: no rules.
 */
final class CreateProductController
{
    public function __construct(
        private CreateProductHandler $handler,
        private ProductRepository $products,
        private Ids $ids,
    ) {
    }

    public function __invoke(CreateProductRequest $request): JsonResponse
    {
        $id = $this->ids->uuid7();

        $this->handler->handle(new CreateProductCommand(
            id: $id,
            title: $request->string('title')->toString(),
            sku: $request->string('sku')->toString(),
            shortDescription: $request->exists('short_description') ? $request->string('short_description')->toString() : null,
            description: $request->exists('description') ? $request->string('description')->toString() : null,
            brand: $request->exists('brand') ? $request->string('brand')->toString() : null,
            barcode: $request->exists('barcode') ? $request->string('barcode')->toString() : null,
            gtin: $request->exists('gtin') ? $request->string('gtin')->toString() : null,
            mpn: $request->exists('mpn') ? $request->string('mpn')->toString() : null,
            priceMinor: $request->integer('price_minor'),
            currency: $request->string('currency', 'XXX')->toString(),
            compareAtMinor: $request->input('compare_at_minor'),
            costMinor: $request->input('cost_minor'),
            saleStartsAt: $request->filled('sale_starts_at') ? new DateTimeImmutable($request->string('sale_starts_at')->toString()) : null,
            saleEndsAt: $request->filled('sale_ends_at') ? new DateTimeImmutable($request->string('sale_ends_at')->toString()) : null,
            taxStatus: ProductTaxStatus::from($request->string('tax_status', 'taxable')->toString()),
            taxClass: $request->exists('tax_class') ? $request->string('tax_class')->toString() : null,
            type: ProductType::from($request->string('type', 'physical')->toString()),
            soldIndividually: $request->boolean('sold_individually'),
            externalUrl: $request->exists('external_url') ? $request->string('external_url')->toString() : null,
            slug: $request->exists('slug') ? $request->string('slug')->toString() : null,
            metaTitle: $request->exists('meta_title') ? $request->string('meta_title')->toString() : null,
            metaDescription: $request->exists('meta_description') ? $request->string('meta_description')->toString() : null,
            visibility: ProductVisibilityMap::fromString(
                $request->string('visibility', 'visible')->toString(),
            ),
            featured: $request->boolean('featured'),
            weightG: $request->input('weight_g'),
            lengthMm: $request->input('length_mm'),
            widthMm: $request->input('width_mm'),
            heightMm: $request->input('height_mm'),
            variantWeightG: $request->input('variant_weight_g'),
            variantLengthMm: $request->input('variant_length_mm'),
            variantWidthMm: $request->input('variant_width_mm'),
            variantHeightMm: $request->input('variant_height_mm'),
            shippingClass: $request->input('shipping_class'),
        ));

        return (new ProductResource($this->products->find($id)))
            ->response()
            ->setStatusCode(201)
            ->header('Location', '/api/v1/products/'.$id);
    }
}
