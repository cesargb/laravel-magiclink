<?php

namespace Cesargb\MagicLink;

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
            __DIR__.'/config/magiclink.php' => config_path('magiclink.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/databases/migrations/create_table_magic_links.php' => database_path('migrations').'/2017_07_06_000000_create_table_magic_links.php',
        ], 'migrations');

        $this->loadRoutesFrom(__DIR__.'/routes.php');
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
