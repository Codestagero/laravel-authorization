<?php

namespace Codestage\Authorization\Tests;

use Codestage\Authorization\Providers\AuthorizationServiceProvider;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use WithFaker;

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->setBasePath(__DIR__ . '/../');
        $this->setUpFaker();
    }

    /**
     * @inheritDoc
     */
    protected function getPackageProviders($app): array
    {
        return [AuthorizationServiceProvider::class];
    }
}
