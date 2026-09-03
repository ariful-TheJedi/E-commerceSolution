<?php
namespace Modules\Product\Application\Taxonomy;
final readonly class AddCollectionProductCommand { public function __construct(public string $collectionId, public string $productId, public int $position) {} }