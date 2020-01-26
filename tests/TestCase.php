<?php

namespace MagicLink\Test;

use Illuminate\Database\Schema\Blueprint;
use MagicLink\MagicLinkServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        // $this->loadRoutes();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            MagicLinkServiceProvider::class,
        ];
    }

    /**
     * Set up the environment.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:mJlbzP1TMXUPouK3KK6e9zS/VvxtWTfzfVlkn1JTqpM=');

        $app['config']->set('auth.providers.users.model', 'MagicLink\Test\User');

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ]);

        $app['config']->set('view.paths', [__DIR__.'/stubs/resources/views']);
        $app['config']->set('filesystems.disks.local.root', __DIR__.'/stubs/storage/app');
    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
        });

        include_once __DIR__.'/../databases/migrations/create_table_magic_links.php';

        (new \CreateTableMagicLinks)->up();

        User::create(['email' => 'test@user.com']);
    }

    protected function loadRoutes()
    {
        include __DIR__.'/stubs/routes.php';
    }
}
