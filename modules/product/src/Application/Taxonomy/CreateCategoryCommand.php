<?php
namespace Modules\Product\Application\Taxonomy;
final readonly class CreateCategoryCommand { public function __construct(public string $id, public string $name, public string $slug, public ?string $parentId, public int $position) {} }