<?php

namespace MagicLink\Test;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MagicLink\MagicLinkServiceProvider;
use MagicLink\Test\TestSupport\User;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
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
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function defineEnvironment($app)
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(
            random_bytes(32)
        ));

        $app['config']->set('auth.providers.users.model', 'MagicLink\Test\TestSupport\User');

        $app['config']->set('view.paths', [__DIR__ . '/stubs/resources/views']);

        $app['config']->set('filesystems.disks.local.root', __DIR__ . '/stubs/storage/app');

        $app['config']->set('filesystems.disks.alternative', [
            'driver' => 'local',
            'root' => __DIR__ . '/stubs/storage/app_alternative',
        ]);

        $app['config']->set('database.connections.pgsql', [
            'driver' => 'pgsql',
            'host' => '127.0.0.1',
            'port' => '54320',
            'username' => 'postgres',
            'password' => 'mysecretpassword',
            'database' => 'test',
        ]);

        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'username' => 'root',
            'password' => '',
            'database' => 'test',
        ]);

        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');

        $driver = getenv('DB_DRIVER');

        if ($driver !== 'pgsql') {
            $app['config']->set('database.default', 'testbench');
        } else {
            $app['config']->set('database.default', $driver);
        }
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../databases/migrations');
    }

    protected function refreshTestDatabase()
    {
        if (getenv('DB_DRIVER') === 'pgsql') {
            $this->app['db']->connection()->getSchemaBuilder()->dropIfExists('users');
            $this->app['db']->connection()->getSchemaBuilder()->dropIfExists('migrations');
            $this->app['db']->connection()->getSchemaBuilder()->dropIfExists('magic_links');
            $this->artisan('migrate', ['--database' => getenv('DB_DRIVER') === 'pgsql' ? 'pgsql' : 'testbench']);
        }
    }

    protected function afterRefreshingDatabase()
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->string('remember_token')->nullable();
        });

        User::create(['email' => 'test@user.com']);

        $this->artisan('migrate', ['--database' => getenv('DB_DRIVER') === 'pgsql' ? 'pgsql' : 'testbench']);
    }

    protected function loadRoutes()
    {
        include __DIR__ . '/stubs/routes.php';
    }
}
