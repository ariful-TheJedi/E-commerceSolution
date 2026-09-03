<?php
namespace Modules\Product\Application\Taxonomy;
final readonly class AddCollectionRuleCommand { public function __construct(public string $id, public string $collectionId, public string $field, public string $operator, public string $value) {} }