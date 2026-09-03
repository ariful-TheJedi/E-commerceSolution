<?php

namespace Modules\Product\Domain\Exceptions;

use DomainException;

/**
 * Thrown when publish() is called on a listing that is not draft.
 * Active and archived cannot be published.
 */
final class CannotPublishProduct extends DomainException
{
}
