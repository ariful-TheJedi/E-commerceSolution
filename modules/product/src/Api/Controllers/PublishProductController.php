<?php

namespace Modules\Product\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Resources\ProductResource;
use Modules\Product\Application\Ports\ProductRepository;
use Modules\Product\Application\PublishProduct\PublishProductCommand;
use Modules\Product\Application\PublishProduct\PublishProductHandler;

/**
 * POST /api/v1/products/{id}/publish — draft → active.
 */
final class PublishProductController
{
    public function __construct(
        private PublishProductHandler $handler,
        private ProductRepository $products,
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        $this->handler->handle(new PublishProductCommand($id));

        return (new ProductResource($this->products->find($id)))
            ->response();
    }
}
