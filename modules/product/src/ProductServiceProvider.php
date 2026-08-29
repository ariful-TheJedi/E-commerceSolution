<?php

namespace Modules\Product;

use Illuminate\Support\ServiceProvider;
use Modules\Product\Contracts\ProductApi;
use Modules\Product\Infrastructure\ProductApiImpl;

final class ProductServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProductApi::class, ProductApiImpl::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Infrastructure/Persistence/Migrations');
        $this->loadRoutesFrom(__DIR__.'/Api/Routes/api.php');
    }
}
