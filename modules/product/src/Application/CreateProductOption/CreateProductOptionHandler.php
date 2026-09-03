<?php

namespace Modules\Product\Application\CreateProductOption;

use InvalidArgumentException;
use Modules\Product\Application\Ports\ProductRepository;
use Modules\Product\Application\Ports\VariantRepository;
use Modules\Product\Domain\ProductOption;

/** Creates product option dimensions; it does not create a variant. */
final class CreateProductOptionHandler
{
    public function __construct(private ProductRepository $products, private VariantRepository $variants) {}

    public function handle(CreateProductOptionCommand $command): void
    {
        if ($this->products->find($command->productId) === null) throw new InvalidArgumentException('Product does not exist.');
        $this->variants->createOption(new ProductOption($command->id, $command->productId, $command->name, $command->position));
    }
}
