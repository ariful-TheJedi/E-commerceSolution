<?php
namespace Modules\Product\Api\Controllers;
use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\CreateCollectionRequest;
use Modules\Product\Application\Taxonomy\{CreateCollectionCommand,TaxonomyHandler};
use Shared\Ids;
/** POST /api/v1/collections. */
final class CreateCollectionController { public function __construct(private TaxonomyHandler $handler, private Ids $ids) {} public function __invoke(CreateCollectionRequest $request): JsonResponse { $id = $this->ids->uuid7(); $this->handler->createCollection(new CreateCollectionCommand($id, $request->string('name')->toString(), $request->string('slug')->toString(), $request->string('kind')->toString(), $request->input('match'))); return response()->json(['data' => ['id' => $id]], 201); } }