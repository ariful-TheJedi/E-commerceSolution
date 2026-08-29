<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Api\Controllers\ListProductsController;

/*
| Product module HTTP routes.
| Module name must never appear in URLs.
*/

Route::middleware('api')
    ->prefix('api/v1')
    ->group(function (): void {
        Route::get('/products', ListProductsController::class)
            ->name('products.index');
    });
