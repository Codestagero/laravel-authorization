<?php

namespace Codestage\Authorization\Tests\Feature;

use Carbon\Carbon;
use Codestage\Authorization\Middleware\AuthorizationMiddleware;
use Codestage\Authorization\Tests\Fakes\Http\Controllers\PolicyAuthorizationTest\{PolicyAuthorizationTestController1};
use Codestage\Authorization\Tests\TestCase;
use Illuminate\Contracts\Routing\{Registrar, UrlGenerator};
use Illuminate\Testing\TestResponse;

/**
 * @coversNothing
 */
class PolicyAuthorizationTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function Authorize_WhenClassRequiresPolicyAndNotAuthenticated_Unauthorized(): void
    {
        // Arrange
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', PolicyAuthorizationTestController1::class)->middleware([AuthorizationMiddleware::class])->name('test1');
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->app->make(UrlGenerator::class);

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson($urlGenerator->route('test1')),
        ];

        // Assert
        $responses[0]->assertUnauthorized();
    }

    /**
     * @test
     * @return void
     */
    public function Authorize_WhenClassRequiresPolicyWhichFails_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser();
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', PolicyAuthorizationTestController1::class)->middleware([AuthorizationMiddleware::class])->name('test1');
        Carbon::setTestNow(Carbon::parse('2001-07-26T00:00:00Z'));
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->app->make(UrlGenerator::class);

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson($urlGenerator->route('test1')),
        ];

        // Assert
        $responses[0]->assertForbidden();
    }

    /**
     * @test
     * @return void
     */
    public function Authorize_WhenClassRequiresPolicyWhichPasses_Success(): void
    {
        // Arrange
        $this->authenticateUser();
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', PolicyAuthorizationTestController1::class)->middleware([AuthorizationMiddleware::class])->name('test1');
        Carbon::setTestNow(Carbon::parse('2001-12-25T00:00:00Z'));
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->app->make(UrlGenerator::class);

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson($urlGenerator->route('test1')),
        ];

        // Assert
        $responses[0]->assertSuccessful();
    }
}
