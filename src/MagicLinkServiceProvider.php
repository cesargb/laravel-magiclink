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

           $this->publishes([
            __DIR__.'/../databases/migrations' => database_path('migrations'),
        ], 'migrations');

        if ($this->mustLoadRoute()) {
            $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');
        }

        // Views
        $sourceViewsPath = __DIR__.'/../resources/views';
        $this->loadViewsFrom($sourceViewsPath, 'magiclink');
        $this->publishes([
            $sourceViewsPath => resource_path('views/vendor/magiclink'),
        ], 'views');
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
