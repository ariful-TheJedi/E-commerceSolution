<?php

namespace Modules\Product\Application\CreateAttributeOption;

use InvalidArgumentException;
use Modules\Product\Application\Ports\SpecificationRepository;
use Modules\Product\Domain\AttributeDataType;
use Modules\Product\Domain\AttributeOption;

/** Adds an enum option and its optional visual swatch metadata. */
final class CreateAttributeOptionHandler
{
    public function __construct(private SpecificationRepository $specifications)
    {
    }

    public function handle(CreateAttributeOptionCommand $command): void
    {
        $definition = $this->specifications->findDefinition($command->attributeId);
        if ($definition === null) {
            throw new InvalidArgumentException('Attribute does not exist.');
        }

        if ($definition->dataType !== AttributeDataType::Enum) {
            throw new InvalidArgumentException('Options are only valid for enum attributes.');
        }

        $this->specifications->addOption(new AttributeOption(
            id: $command->id, attributeId: $command->attributeId, label: $command->label,
            slug: $command->slug, colorHex: $command->colorHex, imagePath: $command->imagePath,
            position: $command->position,
        ));
    }
}
