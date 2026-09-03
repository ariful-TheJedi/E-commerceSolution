<?php

namespace Modules\Product\Infrastructure\Adapters;

use Shared\Ids;
use Symfony\Component\Uid\Uuid;

/**
 * Time-ordered UUIDv7 ids. The HTTP layer asks this when creating a listing.
 */
final class Uuid7Ids implements Ids
{
    public function uuid7(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}
