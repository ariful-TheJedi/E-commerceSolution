<?php

namespace Modules\Product\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\AddDigitalFileRequest;
use Modules\Product\Application\Media\AddDigitalFileCommand;
use Modules\Product\Application\Media\MediaHandler;
use Shared\Ids;

/** POST /api/v1/products/{id}/digital-files. */
final class AddDigitalFileController
{
    public function __construct(private MediaHandler $handler, private Ids $ids) {}
    public function __invoke(AddDigitalFileRequest $request, string $id): JsonResponse { $fileId = $this->ids->uuid7(); $this->handler->addDigitalFile(new AddDigitalFileCommand($fileId, $id, $request->string('path')->toString(), $request->input('variant_id'), $request->input('download_limit'), $request->input('expires_after_days'))); return response()->json(['data' => ['id' => $fileId]], 201); }
}