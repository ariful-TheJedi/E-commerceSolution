<?php

namespace Modules\Product\Domain\Exceptions;

use DomainException;

/**
 * Thrown when archive() is called on a listing that is already archived.
 */
final class CannotArchiveProduct extends DomainException
{
}
