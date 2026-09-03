<?php
namespace Modules\Product\Application\Taxonomy;
final readonly class CreateCollectionCommand { public function __construct(public string $id, public string $name, public string $slug, public string $kind, public ?string $match) {} }