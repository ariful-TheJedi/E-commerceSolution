<?php

use Illuminate\Support\Facades\Route;
use Platform\Http\ServeMediaController;

require base_path('frontend/storefront/routes/web.php');

Route::get(trim((string) config('media.url_prefix'), '/').'/{prefix}/{path}', ServeMediaController::class)
    ->where('prefix', preg_quote((string) config('media.prefix'), '/'))
    ->where('path', '.*')
    ->name('media.show');

Route::view('/admin/{any?}', 'admin')
    ->where('any', '.*')
    ->name('admin');
