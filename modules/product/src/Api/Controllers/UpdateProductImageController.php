<?php

namespace Modules\Product\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\UpdateProductImageRequest;
use Modules\Product\Application\Media\MediaHandler;
use Modules\Product\Application\Media\UpdateImageCommand;

/** PATCH /api/v1/products/{id}/media/{mediaId}. */
final class UpdateProductImageController
{
    public function __construct(private MediaHandler $handler) {}
    public function __invoke(UpdateProductImageRequest $request, string $id, string $mediaId): JsonResponse { $this->handler->updateImage(new UpdateImageCommand($mediaId, $id, $request->exists('position') ? $request->integer('position') : null, $request->exists('is_primary') ? $request->boolean('is_primary') : null, $request->exists('variant_id') ? $request->input('variant_id') : null, $request->exists('alt') ? $request->input('alt') : null)); return response()->json(['data' => ['id' => $mediaId]]); }
}