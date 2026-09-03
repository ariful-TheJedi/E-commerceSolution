<?php
namespace Modules\Product\Application\Operations;
/** Input for a bounded bulk edit of listing flags. */
final readonly class BulkEditCommand { /** @param list<string> $ids */ public function __construct(public array $ids, public ?string $visibility, public ?bool $featured) {} }