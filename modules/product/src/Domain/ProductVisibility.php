<?php

namespace Modules\Product\Domain;

/**
 * Where the listing may appear. Independent of featured and of status.
 *
 * visible  — catalog lists and search
 * catalog  — category / catalog lists only, not search
 * search   — search only, not category lists
 * hidden   — not listed; a direct URL may still open it later
 */
enum ProductVisibility
{
    case Visible;
    case Catalog;
    case Search;
    case Hidden;
}
