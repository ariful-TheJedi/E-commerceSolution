<?php

namespace Modules\Product\Domain;

/**
 * Catalog listing type. Fulfilment and bundle behavior belong to other modules.
 */
enum ProductType: string
{
    case Physical = 'physical';
    case Virtual = 'virtual';
    case Downloadable = 'downloadable';
    case Grouped = 'grouped';
    case Bundle = 'bundle';
    case External = 'external';
}