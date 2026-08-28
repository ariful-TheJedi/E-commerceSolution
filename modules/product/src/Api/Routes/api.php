<?php

use Illuminate\Support\Facades\Route;

/*
| Product module HTTP routes.
| Module name must never appear in URLs. Paths are added with each slice.
*/

Route::middleware('api')
    ->prefix('api/v1')
    ->group(function (): void {
        //
    });
