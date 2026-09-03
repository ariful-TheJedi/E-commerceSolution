<?php

namespace Modules\Product\Domain;

/**
 * Allowed value for an enum attribute, optionally rendered as a swatch.
 */
final readonly class AttributeOption
{
    public function __construct(
        public string $id,
        public string $attributeId,
        public string $label,
        public string $slug,
        public ?string $colorHex = null,
        public ?string $imagePath = null,
        public int $position = 0,
    ) {
    }
}
