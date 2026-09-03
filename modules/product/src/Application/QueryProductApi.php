<?php

namespace Modules\Product\Application;

use Modules\Product\Application\Ports\ProductRepository;
use Modules\Product\Contracts\ProductApi;
use Modules\Product\Domain\ProductStatus;

/**
 * ProductApi for other modules. Reads through the repository port.
 *
 * Not HTTP. Not Eloquent. isActive is “published / sellable at catalog
 * level” — status active. Draft, archived, and unknown ids are not active.
 */
final class QueryProductApi implements ProductApi
{
    public function __construct(
        private ProductRepository $products,
    ) {
    }

    public function isActive(string $id): bool
    {
        return $this->areActive([$id])[$id];
    }

    public function areActive(array $ids): array
    {
        $found = $this->products->findMany($ids);
        $result = [];

        foreach ($ids as $id) {
            $product = $found[$id] ?? null;
            $result[$id] = $product !== null && $product->status() === ProductStatus::Active;
        }

        return $result;
    }
}
