<?php

namespace Modules\Product\Application;

use Modules\Product\Domain\ProductVisibility;

/**
 * Maps HTTP visibility strings to the Domain enum.
 * Api talks to this, not to Domain cases directly.
 */
final class ProductVisibilityMap
{
    public static function fromString(string $value): ProductVisibility
    {
        return match ($value) {
            'catalog' => ProductVisibility::Catalog,
            'search' => ProductVisibility::Search,
            'hidden' => ProductVisibility::Hidden,
            default => ProductVisibility::Visible,
        };
    }
}
