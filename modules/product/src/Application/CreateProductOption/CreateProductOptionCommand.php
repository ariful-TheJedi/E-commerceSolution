<?php

namespace Modules\Product\Application\CreateProductOption;

/** Input for adding an option dimension to a product. */
final readonly class CreateProductOptionCommand
{
    public function __construct(public string $id, public string $productId, public string $name, public int $position = 0) {}
}
