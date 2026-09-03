<?php
namespace Modules\Product\Domain;
use InvalidArgumentException;
/** A hierarchical catalog category. */
final readonly class Category
{
    public function __construct(public string $id, public string $name, public string $slug, public ?string $parentId, public int $position = 0)
    { if ($name === '' || $slug === '' || $position < 0) throw new InvalidArgumentException('Invalid category.'); }
}