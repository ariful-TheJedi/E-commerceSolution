<?php

namespace Modules\Product\Domain;

use InvalidArgumentException;

/** A manual merchandising link from one catalog product to another. */
final readonly class ProductRelation
{
    public function __construct(public string $fromProductId, public string $toProductId, public string $kind)
    {
        if ($fromProductId === $toProductId || !in_array($kind, ['related', 'upsell', 'cross_sell', 'alternative', 'fbt'], true)) {
            throw new InvalidArgumentException('Invalid product relation.');
        }
    }
}