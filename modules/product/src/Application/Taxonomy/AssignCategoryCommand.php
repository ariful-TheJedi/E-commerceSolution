<?php
namespace Modules\Product\Application\Taxonomy;
final readonly class AssignCategoryCommand { public function __construct(public string $productId, public string $categoryId, public bool $canonical, public int $position) {} }