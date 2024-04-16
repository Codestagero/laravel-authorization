<?php

namespace Codestage\Authorization\Tests\Feature;

use Codestage\Authorization\Middleware\AuthorizationMiddleware;
use Codestage\Authorization\Tests\Fakes\Http\Controllers\SimpleAuthorizationTest\{SimpleAuthorizationController1,
    SimpleAuthorizationController2,
    SimpleAuthorizationController3};
use Codestage\Authorization\Tests\TestCase;
use Illuminate\Contracts\Routing\{Registrar, UrlGenerator};
use Illuminate\Testing\TestResponse;

/**
 * @coversNothing
 */
class SimpleAuthorizationTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function Authorize_WhenClassRequiresAuthorizationAndNotAuthenticated_Unauthorized(): void
    {
        // Arrange
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', [SimpleAuthorizationController1::class, 'requiresAuth'])->middleware([AuthorizationMiddleware::class])->name('test1');
        $router->get('test2', [SimpleAuthorizationController1::class, 'requiresAuthAsWell'])->middleware([AuthorizationMiddleware::class])->name('test2');
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->app->make(UrlGenerator::class);

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson($urlGenerator->route('test1')),
            $this->getJson($urlGenerator->route('test2'))
        ];

        // Assert
        $responses[0]->assertUnauthorized();
        $responses[1]->assertUnauthorized();
    }
    /**
     * @test
     * @return void
     */
    public function Authorize_WhenClassRequiresAuthorizationAndIsAuthenticated_Success(): void
    {
        // Arrange
        $this->authenticateUser();
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', [SimpleAuthorizationController1::class, 'requiresAuth'])->middleware([AuthorizationMiddleware::class])->name('test1');
        $router->get('test2', [SimpleAuthorizationController1::class, 'requiresAuthAsWell'])->middleware([AuthorizationMiddleware::class])->name('test2');
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->app->make(UrlGenerator::class);

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson($urlGenerator->route('test1')),
            $this->getJson($urlGenerator->route('test2'))
        ];

        // Assert
        $responses[0]->assertSuccessful();
        $responses[1]->assertSuccessful();
    }

    /**
     * @test
     * @return void
     */
    public function Authorize_WhenMethodRequiresAuthorizationAndNotAuthenticated_Unauthorized(): void
    {
        // Arrange
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', [SimpleAuthorizationController2::class, 'doesNotRequireAuth'])->middleware([AuthorizationMiddleware::class])->name('test1');
        $router->get('test2', [SimpleAuthorizationController2::class, 'requiresAuthAsWell'])->middleware([AuthorizationMiddleware::class])->name('test2');
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->app->make(UrlGenerator::class);

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson($urlGenerator->route('test1')),
            $this->getJson($urlGenerator->route('test2'))
        ];

        // Assert
        $responses[0]->assertSuccessful();
        $responses[1]->assertUnauthorized();
    }
    /**
     * @test
     * @return void
     */
    public function Authorize_WhenMethodRequiresAuthorizationAndIsAuthenticated_Success(): void
    {
        // Arrange
        $this->authenticateUser();
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', [SimpleAuthorizationController2::class, 'doesNotRequireAuth'])->middleware([AuthorizationMiddleware::class])->name('test1');
        $router->get('test2', [SimpleAuthorizationController2::class, 'requiresAuthAsWell'])->middleware([AuthorizationMiddleware::class])->name('test2');
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->app->make(UrlGenerator::class);

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson($urlGenerator->route('test1')),
            $this->getJson($urlGenerator->route('test2'))
        ];

        // Assert
        $responses[0]->assertSuccessful();
        $responses[1]->assertSuccessful();
    }

    /**
     * @test
     * @return void
     */
    public function Authorize_WhenClassRequiresAuthButMethodBypassesItAndNotAuthenticated_Unauthorized(): void
    {
        // Arrange
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', [SimpleAuthorizationController3::class, 'doesNotRequireAuth'])->middleware([AuthorizationMiddleware::class])->name('test1');
        $router->get('test2', [SimpleAuthorizationController3::class, 'requiresAuthAsWell'])->middleware([AuthorizationMiddleware::class])->name('test2');
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->app->make(UrlGenerator::class);

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson($urlGenerator->route('test1')),
            $this->getJson($urlGenerator->route('test2'))
        ];

        // Assert
        $responses[0]->assertSuccessful();
        $responses[1]->assertUnauthorized();
    }
    /**
     * @test
     * @return void
     */
    public function Authorize_WhenClassRequiresAuthButMethodBypassesItAndIsAuthenticated_Success(): void
    {
        // Arrange
        $this->authenticateUser();
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', [SimpleAuthorizationController3::class, 'doesNotRequireAuth'])->middleware([AuthorizationMiddleware::class])->name('test1');
        $router->get('test2', [SimpleAuthorizationController3::class, 'requiresAuthAsWell'])->middleware([AuthorizationMiddleware::class])->name('test2');
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->app->make(UrlGenerator::class);

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson($urlGenerator->route('test1')),
            $this->getJson($urlGenerator->route('test2'))
        ];

        // Assert
        $responses[0]->assertSuccessful();
        $responses[1]->assertSuccessful();
    }
}
