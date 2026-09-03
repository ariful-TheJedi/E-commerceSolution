<?php

namespace Modules\Product\Application\CreateProductOptionValue;

/** Input for adding one selectable value to a product option. */
final readonly class CreateProductOptionValueCommand
{
    public function __construct(public string $id, public string $optionId, public string $label, public string $slug, public ?string $colorHex = null, public ?string $imagePath = null, public int $position = 0) {}
}
