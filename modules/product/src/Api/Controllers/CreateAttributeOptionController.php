<?php

namespace Modules\Product\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\CreateAttributeOptionRequest;
use Modules\Product\Application\CreateAttributeOption\CreateAttributeOptionCommand;
use Modules\Product\Application\CreateAttributeOption\CreateAttributeOptionHandler;
use Shared\Ids;

/** POST /api/v1/attributes/{id}/options; adds a reusable swatch option. */
final class CreateAttributeOptionController
{
    public function __construct(private CreateAttributeOptionHandler $handler, private Ids $ids)
    {
    }

    public function __invoke(CreateAttributeOptionRequest $request, string $id): JsonResponse
    {
        $optionId = $this->ids->uuid7();
        $this->handler->handle(new CreateAttributeOptionCommand(
            id: $optionId, attributeId: $id, label: $request->string('label')->toString(),
            slug: $request->string('slug')->toString(), colorHex: $request->input('color_hex'),
            imagePath: $request->input('image_path'), position: $request->integer('position'),
        ));

        return response()->json(['data' => ['id' => $optionId]], 201);
    }
}
