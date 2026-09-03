<?php

namespace Modules\Product\Application\PublishProduct;

/**
 * Ask to publish this listing. Domain rejects anything that is not draft.
 */
final readonly class PublishProductCommand
{
    public function __construct(
        public string $id,
    ) {
    }
}
