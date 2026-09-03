<?php

namespace Modules\Product\Application\ArchiveProduct;

/**
 * Ask to archive this listing. Domain rejects an already-archived listing.
 */
final readonly class ArchiveProductCommand
{
    public function __construct(
        public string $id,
    ) {
    }
}
