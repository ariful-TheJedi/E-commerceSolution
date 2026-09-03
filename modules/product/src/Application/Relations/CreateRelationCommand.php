<?php

namespace Modules\Product\Application\Relations;

/** Input for one manual product relationship. */
final readonly class CreateRelationCommand
{
    public function __construct(public string $fromProductId, public string $toProductId, public string $kind) {}
}