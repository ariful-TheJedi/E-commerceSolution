<?php

namespace Modules\Product\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\AssignProductAttributeRequest;
use Modules\Product\Application\AssignProductAttribute\AssignProductAttributeCommand;
use Modules\Product\Application\AssignProductAttribute\AssignProductAttributeHandler;
use Shared\Ids;

/** PUT /api/v1/products/{id}/specifications; assigns one reusable fact. */
final class AssignProductAttributeController
{
    public function __construct(private AssignProductAttributeHandler $handler, private Ids $ids)
    {
    }

    public function __invoke(AssignProductAttributeRequest $request, string $id): JsonResponse
    {
        $valueId = $this->ids->uuid7();
        $this->handler->handle(new AssignProductAttributeCommand(
            id: $valueId, productId: $id, attributeId: $request->string('attribute_id')->toString(),
            variantId: $request->input('variant_id'), valueText: $request->input('value'),
            attributeOptionId: $request->input('attribute_option_id'),
        ));

        return response()->json(['data' => ['id' => $valueId]], 201);
    }
}
