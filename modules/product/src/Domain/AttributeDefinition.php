<?php

namespace Modules\Product\Domain;

use InvalidArgumentException;

/**
 * Reusable catalog specification definition. It is not a variant option.
 */
final readonly class AttributeDefinition
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public AttributeDataType $dataType,
        public bool $filterable,
        public bool $sortable,
        public bool $visibleOnPdp,
    ) {
        if (trim($name) === '' || trim($slug) === '') {
            throw new InvalidArgumentException('Attribute name and slug are required.');
        }
    }
}
