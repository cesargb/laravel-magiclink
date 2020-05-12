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

        $app['config']->set('view.paths', [__DIR__.'/stubs/resources/views']);

        $app['config']->set('filesystems.disks.local.root', __DIR__.'/stubs/storage/app');

        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ]);

        $app['config']->set('database.connections.pgsql', [
            'driver'   => 'pgsql',
            'host' => '127.0.0.1',
            'port' => '54320',
            'username' => 'postgres',
            'password' => 'mysecretpassword',
            'database' => 'test',
        ]);

        $app['config']->set('database.connections.mysql', [
            'driver'   => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'username' => 'root',
            'password' => '',
            'database' => 'test',
        ]);

        $app['config']->set('database.default', 'sqlite');

        if (getenv('DB_DRIVER') === 'pgsql') {
            $app['config']->set('database.default', 'pgsql');
        } elseif (getenv('DB_DRIVER') === 'mysql') {
            $app['config']->set('database.default', 'mysql');
        }
    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        if ($app['config']->get('database.default') !== 'sqlite') {
            $app['db']->connection()->getSchemaBuilder()->dropIfExists('users');
            $app['db']->connection()->getSchemaBuilder()->dropIfExists('migrations');
            $app['db']->connection()->getSchemaBuilder()->dropIfExists('magic_links');
        }

        $this->artisan('migrate');

        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
        });

        User::create(['email' => 'test@user.com']);
    }

    protected function loadRoutes()
    {
        include __DIR__.'/stubs/routes.php';
    }
}
