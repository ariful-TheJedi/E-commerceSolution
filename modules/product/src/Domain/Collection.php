<?php
namespace Modules\Product\Domain;
use InvalidArgumentException;
/** A manual or rule-driven catalog collection. */
final readonly class Collection
{
    public function __construct(public string $id, public string $name, public string $slug, public string $kind, public ?string $match)
    { if ($name === '' || $slug === '' || !in_array($kind, ['manual', 'automatic'], true) || ($kind === 'automatic' && !in_array($match, ['all', 'any'], true))) throw new InvalidArgumentException('Invalid collection.'); }
}