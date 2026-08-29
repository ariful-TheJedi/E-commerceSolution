<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Contracts\ProductApi;

Route::get('/', function (ProductApi $products) {
    return view('welcome', [
        'products' => $products->listActiveSummaries(),
    ]);
});
