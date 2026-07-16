<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    private const TEST_DATABASE = 'ristopilot_tenant_testing';

    /**
     * Boot testing traits only after verifying that destructive database
     * operations cannot target the local development database.
     */
    protected function setUpTraits()
    {
        $this->assertSafeTestingDatabase();

        return parent::setUpTraits();
    }

    private function assertSafeTestingDatabase(): void
    {
        $connection = (string) config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        if (! app()->environment('testing') || $database !== self::TEST_DATABASE) {
            throw new RuntimeException(sprintf(
                'Unsafe test database configuration: environment [%s], connection [%s], database [%s]. Expected testing/%s. Tests were stopped before migrations.',
                app()->environment(),
                $connection,
                $database,
                self::TEST_DATABASE,
            ));
        }
    }
}
