<?php

namespace Modules\Product\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\AddProductImageRequest;
use Modules\Product\Application\Media\AddImageCommand;
use Modules\Product\Application\Media\MediaHandler;
use Shared\Ids;

/** POST /api/v1/products/{id}/media. */
final class AddProductImageController
{
    public function __construct(private MediaHandler $handler, private Ids $ids) {}
    public function __invoke(AddProductImageRequest $request, string $id): JsonResponse { $mediaId = $this->ids->uuid7(); $this->handler->addImage(new AddImageCommand($mediaId, $id, $request->string('path')->toString(), $request->input('variant_id'), $request->input('alt'), $request->integer('position', 0), $request->boolean('is_primary'))); return response()->json(['data' => ['id' => $mediaId]], 201); }
}