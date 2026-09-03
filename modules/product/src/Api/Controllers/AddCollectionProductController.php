<?php
namespace Modules\Product\Api\Controllers;
use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\CollectionProductRequest;
use Modules\Product\Application\Taxonomy\{AddCollectionProductCommand,TaxonomyHandler};
/** PUT /api/v1/collections/{id}/products/{productId}. */
final class AddCollectionProductController { public function __construct(private TaxonomyHandler $handler) {} public function __invoke(CollectionProductRequest $request, string $id, string $productId): JsonResponse { $this->handler->addCollectionProduct(new AddCollectionProductCommand($id, $productId, $request->integer('position', 0))); return response()->json(['data' => ['collection_id' => $id, 'product_id' => $productId]]); } }