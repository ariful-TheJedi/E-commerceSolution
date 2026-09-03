<?php
namespace Modules\Product\Api\Controllers;
use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\CreateCategoryRequest;
use Modules\Product\Application\Taxonomy\{CreateCategoryCommand,TaxonomyHandler};
use Shared\Ids;
/** POST /api/v1/categories. */
final class CreateCategoryController { public function __construct(private TaxonomyHandler $handler, private Ids $ids) {} public function __invoke(CreateCategoryRequest $request): JsonResponse { $id = $this->ids->uuid7(); $this->handler->createCategory(new CreateCategoryCommand($id, $request->string('name')->toString(), $request->string('slug')->toString(), $request->input('parent_id'), $request->integer('position', 0))); return response()->json(['data' => ['id' => $id]], 201); } }