<?php
namespace Modules\Product\Api\Controllers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Product\Api\Resources\ProductResource;
use Modules\Product\Application\Ports\OperationsRepository;
/** GET /api/v1/admin/products — cursor-paginated admin listing. */
final class AdminProductListController { public function __construct(private OperationsRepository $operations) {} public function __invoke(Request $request): JsonResponse { $page = $this->operations->page($request->query('cursor'), max(1, min((int) $request->query('limit', 25), 100))); return response()->json(['data' => array_map(fn ($product) => (new ProductResource($product))->toArray($request), $page['items']), 'next_cursor' => $page['next_cursor']]); } }