<?php

namespace Modules\Product\Domain;

use InvalidArgumentException;

/**
 * A typed specification assigned to a product or one of its variants.
 */
final readonly class ProductAttributeValue
{
    public function __construct(
        public string $id,
        public string $productId,
        public string $attributeId,
        public ?string $variantId,
        public ?string $valueText,
        public ?string $attributeOptionId,
    ) {
        if ($valueText === null && $attributeOptionId === null) {
            throw new InvalidArgumentException('A specification needs a value or option.');
        }

        if ($valueText !== null && $attributeOptionId !== null) {
            throw new InvalidArgumentException('A specification cannot use both value and option.');
        }
    }
}
