<?php
namespace Modules\Product\Api\Controllers;
use Illuminate\Http\JsonResponse;
use Modules\Product\Application\Taxonomy\{AssignTagCommand,TaxonomyHandler};
/** PUT /api/v1/products/{id}/tags/{tagId}. */
final class AssignTagController { public function __construct(private TaxonomyHandler $handler) {} public function __invoke(string $id, string $tagId): JsonResponse { $this->handler->assignTag(new AssignTagCommand($id, $tagId)); return response()->json(['data' => ['product_id' => $id, 'tag_id' => $tagId]]); } }