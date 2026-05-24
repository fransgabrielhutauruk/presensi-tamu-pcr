<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $testingDatabasePath = __DIR__ . '/../database/testing.sqlite';

        if (! file_exists($testingDatabasePath)) {
            touch($testingDatabasePath);
        }

        // Force testing DB env before the app boots to avoid touching non-test databases.
        putenv('APP_ENV=testing');
        putenv('DB_CONNECTION=sqlite');
        putenv("DB_DATABASE={$testingDatabasePath}");
        $_ENV['APP_ENV'] = 'testing';
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = $testingDatabasePath;
        $_SERVER['APP_ENV'] = 'testing';
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = $testingDatabasePath;

        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Guard rail: fail fast if test runtime is not isolated.
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', $testingDatabasePath);

        $defaultConnection = (string) $app['config']->get('database.default');
        $sqliteDatabase = (string) $app['config']->get('database.connections.sqlite.database');

        if ($defaultConnection !== 'sqlite' || $sqliteDatabase !== $testingDatabasePath) {
            throw new RuntimeException(
                "Unsafe test database configuration detected. Expected sqlite file [{$testingDatabasePath}] for tests."
            );
        }

        return $app;
    }
}
