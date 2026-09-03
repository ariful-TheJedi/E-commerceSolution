<?php

namespace Modules\Product\Application\Exceptions;

use DomainException;

/**
 * A product slug is already used by another product.
 */
final class DuplicateSlug extends DomainException
{
}