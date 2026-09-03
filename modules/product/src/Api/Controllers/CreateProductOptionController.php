<?php

namespace Modules\Product\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\CreateProductOptionRequest;
use Modules\Product\Application\CreateProductOption\CreateProductOptionCommand;
use Modules\Product\Application\CreateProductOption\CreateProductOptionHandler;
use Shared\Ids;

/** POST /api/v1/products/{id}/options. */
final class CreateProductOptionController
{
    public function __construct(private CreateProductOptionHandler $handler, private Ids $ids) {}
    public function __invoke(CreateProductOptionRequest $request, string $id): JsonResponse { $optionId = $this->ids->uuid7(); $this->handler->handle(new CreateProductOptionCommand($optionId, $id, $request->string('name')->toString(), $request->integer('position'))); return response()->json(['data' => ['id' => $optionId]], 201); }
}
