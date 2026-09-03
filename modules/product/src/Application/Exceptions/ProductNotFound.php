<?php

namespace Modules\Product\Application\Exceptions;

use RuntimeException;

/**
 * No listing exists for this id. Not a domain rule — the catalog has no row.
 */
final class ProductNotFound extends RuntimeException
{
}
