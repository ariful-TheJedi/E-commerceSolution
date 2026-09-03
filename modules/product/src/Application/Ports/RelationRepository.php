<?php

namespace Modules\Product\Application\Ports;

use Modules\Product\Domain\ProductRelation;

/** Persists manual product-to-product merchandising links. */
interface RelationRepository
{
    public function productExists(string $productId): bool;
    public function save(ProductRelation $relation): void;
}