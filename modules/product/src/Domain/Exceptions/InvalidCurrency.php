<?php

namespace Modules\Product\Domain\Exceptions;

use DomainException;

/**
 * Catalog money uses an uppercase three-letter currency code.
 */
final class InvalidCurrency extends DomainException
{
}