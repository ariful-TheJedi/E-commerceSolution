<?php

namespace Platform;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

final class PlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app['config']->set('filesystems.disks.media', [
            'driver' => 'local',
            'root' => (string) $this->app['config']->get('media.path'),
            'url' => (string) $this->app['config']->get('media.url'),
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ]);
    }

    public function boot(): void
    {
        File::ensureDirectoryExists((string) config('media.path'));
    }
}
