<?php

namespace Modules\Product\Contracts\Dto;

/**
 * Readonly product summary for other modules and HTTP resources.
 */
final readonly class ProductSummaryDto
{
    public function __construct(
        public string $id,
        public string $title,
        public string $slug,
        public string $status,
        public string $description,
        public int $priceMinor,
        public string $currency,
    ) {}
}
