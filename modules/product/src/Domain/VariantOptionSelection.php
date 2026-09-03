<?php

namespace Modules\Product\Domain;

/** One option value selected by a sellable variant. */
final readonly class VariantOptionSelection
{
    public function __construct(
        public string $variantId,
        public string $optionValueId,
        public string $optionId,
    ) {
    }
}
