<?php

namespace Modules\Product\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Product\Api\Resources\ProductResource;
use Modules\Product\Application\DraftProduct\DraftProductCommand;
use Modules\Product\Application\DraftProduct\DraftProductHandler;
use Modules\Product\Application\Ports\ProductRepository;

/**
 * POST /api/v1/products/{id}/draft — active or archived → draft.
 */
final class DraftProductController
{
    public function __construct(
        private DraftProductHandler $handler,
        private ProductRepository $products,
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        $this->handler->handle(new DraftProductCommand($id));

        return (new ProductResource($this->products->find($id)))
            ->response();
    }
}
