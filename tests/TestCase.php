<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Support\TestDatabaseBootstrapper;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        TestDatabaseBootstrapper::bootstrap();
    }

    protected function beforeRefreshingDatabase()
    {
        TestDatabaseBootstrapper::bootstrap();
    }
}
