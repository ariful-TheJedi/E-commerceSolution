<?php
namespace Modules\Product\Api\Controllers;
use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Requests\DuplicateProductRequest;
use Modules\Product\Application\Operations\{DuplicateProductCommand,OperationsHandler};
use Shared\Ids;
/** POST /api/v1/admin/products/{id}/duplicate. */
final class DuplicateProductController { public function __construct(private OperationsHandler $handler, private Ids $ids) {} public function __invoke(DuplicateProductRequest $request, string $id): JsonResponse { $newId = $this->handler->duplicate(new DuplicateProductCommand($id, $this->ids->uuid7(), $request->string('sku')->toString(), $request->string('slug')->toString())); return response()->json(['data' => ['id' => $newId]], 201); } }