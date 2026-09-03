<?php

namespace Modules\Product\Domain;

use InvalidArgumentException;

/** Downloadable catalog metadata. Orders owns post-purchase entitlement. */
final readonly class DigitalFile
{
    public function __construct(
        public string $id,
        public string $productId,
        public string $path,
        public ?string $variantId,
        public ?int $downloadLimit,
        public ?int $expiresAfterDays,
    ) {
        if ($path === '' || ($downloadLimit !== null && $downloadLimit < 1) || ($expiresAfterDays !== null && $expiresAfterDays < 1)) {
            throw new InvalidArgumentException('Invalid digital file metadata.');
        }
    }
}