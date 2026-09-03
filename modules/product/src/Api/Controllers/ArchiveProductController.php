<?php

namespace Modules\Product\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Resources\ProductResource;
use Modules\Product\Application\ArchiveProduct\ArchiveProductCommand;
use Modules\Product\Application\ArchiveProduct\ArchiveProductHandler;
use Modules\Product\Application\Ports\ProductRepository;

/**
 * POST /api/v1/products/{id}/archive — draft or active → archived.
 */
final class ArchiveProductController
{
    public function __construct(
        private ArchiveProductHandler $handler,
        private ProductRepository $products,
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        $this->handler->handle(new ArchiveProductCommand($id));

        return (new ProductResource($this->products->find($id)))
            ->response();
    }
}
