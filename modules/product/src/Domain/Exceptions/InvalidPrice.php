<?php

namespace Modules\Product\Domain\Exceptions;

use DomainException;

/**
 * A catalog money amount must be a non-negative integer minor-unit value.
 */
final class InvalidPrice extends DomainException
{
}