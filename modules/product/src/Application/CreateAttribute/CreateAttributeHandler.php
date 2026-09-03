<?php

namespace Modules\Product\Application\CreateAttribute;

use Modules\Product\Application\Ports\SpecificationRepository;
use Modules\Product\Domain\AttributeDefinition;

/** Registers reusable attribute metadata; it does not assign product values. */
final class CreateAttributeHandler
{
    public function __construct(private SpecificationRepository $specifications)
    {
    }

    public function handle(CreateAttributeCommand $command): void
    {
        $this->specifications->createDefinition(new AttributeDefinition(
            id: $command->id,
            name: $command->name,
            slug: $command->slug,
            dataType: $command->dataType,
            filterable: $command->filterable,
            sortable: $command->sortable,
            visibleOnPdp: $command->visibleOnPdp,
        ));
    }
}
