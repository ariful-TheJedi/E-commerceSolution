<?php

namespace Modules\Product\Application\DraftProduct;

/**
 * Ask to return this listing to draft (from active or archived).
 */
final readonly class DraftProductCommand
{
    public function __construct(
        public string $id,
    ) {
    }
}
