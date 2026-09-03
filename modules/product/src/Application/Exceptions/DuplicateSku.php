<?php

namespace Modules\Product\Application\Exceptions;

use RuntimeException;

/**
 * SKU is already used on another variant. Uniqueness needs the catalog;
 * Domain only rejects an empty SKU.
 */
final class DuplicateSku extends RuntimeException
{
}
