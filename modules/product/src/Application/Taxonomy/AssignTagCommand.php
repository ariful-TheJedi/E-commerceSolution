<?php
namespace Modules\Product\Application\Taxonomy;
final readonly class AssignTagCommand { public function __construct(public string $productId, public string $tagId) {} }