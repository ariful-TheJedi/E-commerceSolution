<?php

namespace Modules\Product\Application\AssignProductAttribute;

/** Input for assigning a typed specification to a product or variant. */
final readonly class AssignProductAttributeCommand
{
    public function __construct(
        public string $id,
        public string $productId,
        public string $attributeId,
        public ?string $variantId = null,
        public ?string $valueText = null,
        public ?string $attributeOptionId = null,
    ) {
    }
}
