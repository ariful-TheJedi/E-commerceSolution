<?php

namespace Modules\Product\Domain\Exceptions;

use DomainException;

/**
 * SKU is required on the sellable variant. Whitespace is empty.
 * Uniqueness is not this exception — that needs the catalog (Application).
 */
final class EmptySku extends DomainException
{
}
