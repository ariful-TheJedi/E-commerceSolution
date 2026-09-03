<?php
namespace Modules\Product\Api\Controllers;
use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\ImportProductsRequest;
use Modules\Product\Application\Operations\{ImportProductsCommand,OperationsHandler};
/** POST /api/v1/admin/products/import. */
final class ImportProductsController { public function __construct(private OperationsHandler $handler) {} public function __invoke(ImportProductsRequest $request): JsonResponse { return response()->json(['data' => ['imported' => $this->handler->import(new ImportProductsCommand($request->string('csv')->toString()))]], 201); } }