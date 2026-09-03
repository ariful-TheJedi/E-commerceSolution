<?php

namespace Modules\Product\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\CreateVariantRequest;
use Modules\Product\Application\CreateVariant\CreateVariantCommand;
use Modules\Product\Application\CreateVariant\CreateVariantHandler;
use Shared\Ids;

/** POST /api/v1/products/{id}/variants. */
final class CreateVariantController
{
    public function __construct(private CreateVariantHandler $handler, private Ids $ids) {}
    public function __invoke(CreateVariantRequest $request, string $id): JsonResponse { $variantId = $this->ids->uuid7(); $this->handler->handle(new CreateVariantCommand($variantId, $id, $request->string('sku')->toString(), $request->input('option_value_ids', []), $request->boolean('is_default'), $request->input('barcode'), $request->input('gtin'), $request->input('mpn'), $request->integer('price_minor'), $request->string('currency', 'XXX')->toString())); return response()->json(['data' => ['id' => $variantId]], 201); }
}
