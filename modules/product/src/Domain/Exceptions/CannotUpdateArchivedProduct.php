<?php

namespace Modules\Product\Domain\Exceptions;

use DomainException;

/**
 * Thrown when listing flags, copy, or SKU codes are changed on an archived listing.
 * Bring it back to draft first.
 */
final class CannotUpdateArchivedProduct extends DomainException
{
}
