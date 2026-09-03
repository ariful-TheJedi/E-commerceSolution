<?php
namespace Modules\Product\Api\Controllers;
use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\BulkEditProductsRequest;
use Modules\Product\Application\Operations\{BulkEditCommand,OperationsHandler};
/** PATCH /api/v1/admin/products/bulk. */
final class BulkEditProductsController { public function __construct(private OperationsHandler $handler) {} public function __invoke(BulkEditProductsRequest $request): JsonResponse { $this->handler->bulkEdit(new BulkEditCommand($request->input('ids'), $request->input('visibility'), $request->exists('featured') ? $request->boolean('featured') : null)); return response()->json(['data' => ['updated' => count($request->input('ids'))]]); } }