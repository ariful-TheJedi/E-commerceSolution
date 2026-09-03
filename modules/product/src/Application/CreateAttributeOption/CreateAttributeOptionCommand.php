<?php

namespace Modules\Product\Application\CreateAttributeOption;

/** Input for adding an optional swatch value to a reusable attribute. */
final readonly class CreateAttributeOptionCommand
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
