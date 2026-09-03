<?php

namespace Modules\Product\Domain\Exceptions;

use DomainException;

/**
 * A listing must have a non-empty title. Whitespace is empty.
 */
final class EmptyTitle extends DomainException
{
}
