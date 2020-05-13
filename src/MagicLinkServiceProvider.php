<?php

namespace MagicLink;

use Illuminate\Support\ServiceProvider;

class MagicLinkServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/magiclink.php' => config_path('magiclink.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__.'/../databases/migrations');

        if ($this->mustLoadRoute()) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }

    protected function mustLoadRoute()
    {
        return ! config('magiclink.disable_default_route', false);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
