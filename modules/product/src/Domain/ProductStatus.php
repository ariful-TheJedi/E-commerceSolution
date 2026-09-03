<?php

namespace Modules\Product\Domain;

/**
 * Lifecycle of a catalog listing. One of these, never a mix.
 *
 * draft    — working copy; not for sale
 * active   — published; may be sold (stock lives in Inventory)
 * archived — history only; not sold, listing flags frozen
 */
enum ProductStatus
{
    case Draft;
    case Active;
    case Archived;
}
