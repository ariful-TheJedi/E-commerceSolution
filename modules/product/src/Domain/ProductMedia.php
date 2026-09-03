<?php

namespace Modules\Product\Domain;

use InvalidArgumentException;

/** Catalog media metadata. Bytes are stored by the platform media disk. */
final readonly class ProductMedia
{
    public function __construct(
        public string $id,
        public string $productId,
        public string $path,
        public ?string $variantId,
        public ?string $alt,
        public int $position,
        public bool $isPrimary,
        public string $kind = 'image',
    ) {
        if ($path === '' || $position < 0 || !in_array($kind, ['image', 'video'], true)) {
            throw new InvalidArgumentException('Invalid product media.');
        }
    }
}