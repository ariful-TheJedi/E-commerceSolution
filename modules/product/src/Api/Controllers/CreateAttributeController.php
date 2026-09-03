<?php

namespace Modules\Product\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\CreateAttributeRequest;
use Modules\Product\Application\CreateAttribute\CreateAttributeCommand;
use Modules\Product\Application\CreateAttribute\CreateAttributeHandler;
use Modules\Product\Domain\AttributeDataType;
use Shared\Ids;

/** POST /api/v1/attributes; creates reusable specification metadata. */
final class CreateAttributeController
{
    public function __construct(private CreateAttributeHandler $handler, private Ids $ids)
    {
    }

    public function __invoke(CreateAttributeRequest $request): JsonResponse
    {
        $id = $this->ids->uuid7();
        $this->handler->handle(new CreateAttributeCommand(
            id: $id, name: $request->string('name')->toString(), slug: $request->string('slug')->toString(),
            dataType: AttributeDataType::from($request->string('data_type')->toString()),
            filterable: $request->boolean('filterable'), sortable: $request->boolean('sortable'),
            visibleOnPdp: $request->has('visible_on_pdp') ? $request->boolean('visible_on_pdp') : true,
        ));

        return response()->json(['data' => ['id' => $id]], 201)->header('Location', '/api/v1/attributes/'.$id);
    }
}
