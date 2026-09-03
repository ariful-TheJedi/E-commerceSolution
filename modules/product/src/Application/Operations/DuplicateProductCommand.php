<?php
namespace Modules\Product\Application\Operations;
/** Input for duplicating a catalog listing with fresh identity fields. */
final readonly class DuplicateProductCommand { public function __construct(public string $sourceId, public string $id, public string $sku, public string $slug) {} }