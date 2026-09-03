<?php

namespace Modules\Product\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\CreateProductOptionValueRequest;
use Modules\Product\Application\CreateProductOptionValue\CreateProductOptionValueCommand;
use Modules\Product\Application\CreateProductOptionValue\CreateProductOptionValueHandler;
use Shared\Ids;

/** POST /api/v1/product-options/{id}/values. */
final class CreateProductOptionValueController
{
    public function __construct(private CreateProductOptionValueHandler $handler, private Ids $ids) {}
    public function __invoke(CreateProductOptionValueRequest $request, string $id): JsonResponse { $valueId = $this->ids->uuid7(); $this->handler->handle(new CreateProductOptionValueCommand($valueId, $id, $request->string('label')->toString(), $request->string('slug')->toString(), $request->input('color_hex'), $request->input('image_path'), $request->integer('position'))); return response()->json(['data' => ['id' => $valueId]], 201); }
}
