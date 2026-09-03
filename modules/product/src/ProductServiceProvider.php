<?php

namespace Modules\Product;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Product\Application\Ports\Outbox;
use Modules\Product\Application\Ports\ProductRepository;
use Modules\Product\Application\Ports\Transaction;
use Modules\Product\Application\Ports\SpecificationRepository;
use Modules\Product\Application\Ports\SeoRedirects;
use Modules\Product\Application\QueryProductApi;
use Modules\Product\Contracts\ProductApi;
use Modules\Product\Infrastructure\Adapters\PlatformOutbox;
use Modules\Product\Infrastructure\Adapters\ProductConnectionTransaction;
use Modules\Product\Infrastructure\Adapters\SystemClock;
use Modules\Product\Infrastructure\Adapters\Uuid7Ids;
use Modules\Product\Infrastructure\Adapters\EloquentSeoRedirects;
use Modules\Product\Infrastructure\Persistence\Repositories\EloquentProductRepository;
use Modules\Product\Application\Ports\VariantRepository;
use Modules\Product\Infrastructure\Persistence\Repositories\EloquentVariantRepository;
use Modules\Product\Infrastructure\Persistence\Repositories\EloquentSpecificationRepository;
use Modules\Product\Application\Ports\MediaRepository;
use Modules\Product\Infrastructure\Persistence\Repositories\EloquentMediaRepository;
use Modules\Product\Application\Ports\TaxonomyRepository;
use Modules\Product\Infrastructure\Persistence\Repositories\EloquentTaxonomyRepository;
use Modules\Product\Application\Ports\RelationRepository;
use Modules\Product\Infrastructure\Persistence\Repositories\EloquentRelationRepository;
use Modules\Product\Application\Ports\OperationsRepository;
use Modules\Product\Infrastructure\Persistence\Repositories\EloquentOperationsRepository;
use Shared\Clock;
use Shared\Ids;

/**
 * Registers Product routes, ProductApi, and port bindings.
 */
final class ProductServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProductRepository::class, EloquentProductRepository::class);
        $this->app->singleton(VariantRepository::class, EloquentVariantRepository::class);
        $this->app->singleton(SpecificationRepository::class, EloquentSpecificationRepository::class);
        $this->app->singleton(MediaRepository::class, EloquentMediaRepository::class);
        $this->app->singleton(TaxonomyRepository::class, EloquentTaxonomyRepository::class);
        $this->app->singleton(RelationRepository::class, EloquentRelationRepository::class);
        $this->app->singleton(OperationsRepository::class, EloquentOperationsRepository::class);
        $this->app->singleton(Outbox::class, PlatformOutbox::class);
        $this->app->singleton(Transaction::class, ProductConnectionTransaction::class);
        $this->app->singleton(SeoRedirects::class, EloquentSeoRedirects::class);
        $this->app->singleton(ProductApi::class, QueryProductApi::class);
        $this->app->singleton(Ids::class, Uuid7Ids::class);
        $this->app->singleton(Clock::class, SystemClock::class);
    }

    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api/v1')
            ->group(__DIR__.'/Api/Routes/api.php');
    }
}
