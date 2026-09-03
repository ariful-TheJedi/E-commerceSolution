<?php

namespace Modules\Product\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\CreateRelationRequest;
use Modules\Product\Application\Relations\{CreateRelationCommand, RelationHandler};

/** PUT /api/v1/products/{id}/relationships/{toProductId}. */
final class CreateRelationController
{
    public function __construct(private RelationHandler $handler) {}
    public function __invoke(CreateRelationRequest $request, string $id, string $toProductId): JsonResponse { $this->handler->handle(new CreateRelationCommand($id, $toProductId, $request->string('kind')->toString())); return response()->json(['data' => ['from_product_id' => $id, 'to_product_id' => $toProductId, 'kind' => $request->string('kind')->toString()]], 201); }
}