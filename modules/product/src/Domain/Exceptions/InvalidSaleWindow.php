<?php

namespace Modules\Product\Domain\Exceptions;

use DomainException;

/**
 * A sale window must provide both UTC boundaries in chronological order.
 */
final class InvalidSaleWindow extends DomainException
{
}