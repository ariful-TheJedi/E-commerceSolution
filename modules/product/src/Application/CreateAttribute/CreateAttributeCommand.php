<?php

namespace Modules\Product\Application\CreateAttribute;

use Modules\Product\Domain\AttributeDataType;

/** Input for registering a reusable catalog attribute. */
final readonly class CreateAttributeCommand
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public AttributeDataType $dataType,
        public bool $filterable = false,
        public bool $sortable = false,
        public bool $visibleOnPdp = true,
    ) {
    }
}
