<?php
namespace Modules\Product\Domain;
use InvalidArgumentException;
/** A flat catalog tag. */
final readonly class Tag
{
    public function __construct(public string $id, public string $name, public string $slug)
    { if ($name === '' || $slug === '') throw new InvalidArgumentException('Invalid tag.'); }
}