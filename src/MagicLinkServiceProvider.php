<?php

namespace MagicLink;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class MagicLinkServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/magiclink.php', 'magiclink');
    }

    public function boot()
    {
        $this->offerPublishing();

        $this->registerRateLimit();

        $this->loadRouteMagicLink();

        $this->loadViewMagicLink();
    }

    private function registerRateLimit(): void
    {
        $rateLimit = config('magiclink.rate_limit', 'none');

        RateLimiter::for(
            'magiclink',
            fn () => $rateLimit === 'none'
                ? Limit::none()
                : Limit::perMinute($rateLimit)
        );
    }

    private function loadRouteMagicLink(): void
    {
        $disableRegisterRoute = config('magiclink.disable_default_route', false);

        if ($disableRegisterRoute) {
            return;
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');
    }

    private function loadViewMagicLink(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'magiclink');
    }

    private function offerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/magiclink.php' => config_path('magiclink.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../databases/migrations' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/magiclink'),
        ], 'views');
    }
}
