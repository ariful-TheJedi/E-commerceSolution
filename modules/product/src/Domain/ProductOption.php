<?php

namespace Modules\Product\Domain;

use InvalidArgumentException;

/** A product-specific selectable dimension such as size or color. */
final readonly class ProductOption
{
    public function __construct(
        public string $id,
        public string $productId,
        public string $name,
        public int $position = 0,
    ) {
        if (trim($name) === '') {
            throw new InvalidArgumentException('Option name is required.');
        }
    }
}
