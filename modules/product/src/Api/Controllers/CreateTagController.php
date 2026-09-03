<?php
namespace Modules\Product\Api\Controllers;
use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\CreateTagRequest;
use Modules\Product\Application\Taxonomy\{CreateTagCommand,TaxonomyHandler};
use Shared\Ids;
/** POST /api/v1/tags. */
final class CreateTagController { public function __construct(private TaxonomyHandler $handler, private Ids $ids) {} public function __invoke(CreateTagRequest $request): JsonResponse { $id = $this->ids->uuid7(); $this->handler->createTag(new CreateTagCommand($id, $request->string('name')->toString(), $request->string('slug')->toString())); return response()->json(['data' => ['id' => $id]], 201); } }