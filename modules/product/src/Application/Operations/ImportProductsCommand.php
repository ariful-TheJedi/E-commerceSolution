<?php
namespace Modules\Product\Application\Operations;
/** Input CSV for importing product title, SKU, slug, visibility, and featured. */
final readonly class ImportProductsCommand { public function __construct(public string $csv) {} }