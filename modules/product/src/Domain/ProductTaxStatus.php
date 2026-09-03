<?php

namespace Modules\Product\Domain;

/**
 * Whether a catalog product is subject to tax. Tax rates belong to Orders.
 */
enum ProductTaxStatus: string
{
    case Taxable = 'taxable';
    case None = 'none';
}