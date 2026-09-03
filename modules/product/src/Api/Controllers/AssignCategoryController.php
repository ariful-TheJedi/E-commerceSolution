<?php
namespace Modules\Product\Api\Controllers;
use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\CategoryAssignmentRequest;
use Modules\Product\Application\Taxonomy\{AssignCategoryCommand,TaxonomyHandler};
/** PUT /api/v1/products/{id}/categories/{categoryId}. */
final class AssignCategoryController { public function __construct(private TaxonomyHandler $handler) {} public function __invoke(CategoryAssignmentRequest $request, string $id, string $categoryId): JsonResponse { $this->handler->assignCategory(new AssignCategoryCommand($id, $categoryId, $request->boolean('canonical'), $request->integer('position', 0))); return response()->json(['data' => ['product_id' => $id, 'category_id' => $categoryId]]); } }