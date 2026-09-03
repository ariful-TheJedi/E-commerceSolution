<?php
namespace Modules\Product\Application\Taxonomy;
final readonly class CreateTagCommand { public function __construct(public string $id, public string $name, public string $slug) {} }