<?php

namespace Codestage\Authorization\Tests\Feature;

use Codestage\Authorization\Middleware\AuthorizationMiddleware;
use Codestage\Authorization\Tests\Fakes\Http\Controllers\RoleAuthorizationTest\{RoleAuthorizationController1,
    RoleAuthorizationController2,
    RoleAuthorizationController3,
    RoleAuthorizationController4};
use Codestage\Authorization\Tests\TestCase;
use Illuminate\Contracts\Routing\{Registrar, UrlGenerator};
use Illuminate\Testing\TestResponse;

/**
 * @coversNothing
 */
class RoleAuthorizationTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function OnlyClassGuard_WhenClassRequiresAuthorizationAndNotAuthenticated_Unauthorized(): void
    {
        // Arrange
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', RoleAuthorizationController1::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
    public function OnlyClassGuard_WhenClassRequiresAuthorizationAndDoesNotHaveRoles_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser();
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', RoleAuthorizationController1::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
    public function OnlyClassGuard_WhenClassRequiresAuthorizationValid_Success(): void
    {
        // Arrange
        $this->authenticateUser(roles: ['test-role-1']);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', RoleAuthorizationController1::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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

    /**
     * @test
     * @return void
     */
    public function MultipleOptional_WhenClassRequiresAuthorizationAndNotAuthenticated_Unauthorized(): void
    {
        // Arrange
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', RoleAuthorizationController2::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
    public function MultipleOptional_WhenClassRequiresAuthorizationAndDoesNotHaveRoles_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser();
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', RoleAuthorizationController2::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
    public function MultipleOptional_WhenClassRequiresAuthorizationAndHasNoValidRole_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser(roles: ['test-role-2']);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', RoleAuthorizationController2::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
    public function MultipleOptional_WhenClassRequiresAuthorizationAndHasOneRole_Success(): void
    {
        // Arrange
        $this->authenticateUser(roles: ['test-role-1']);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', RoleAuthorizationController2::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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

    /**
     * @test
     * @return void
     */
    public function MultipleOptional_WhenClassRequiresAuthorizationAndHasOtherRole_Success(): void
    {
        // Arrange
        $this->authenticateUser(roles: ['test-role-3']);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', RoleAuthorizationController2::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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

    /**
     * @test
     * @return void
     */
    public function MultipleOptional_WhenClassRequiresAuthorizationAndHasBothRoles_Success(): void
    {
        // Arrange
        $this->authenticateUser(roles: ['test-role-1', 'test-role-3']);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', RoleAuthorizationController2::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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

    /**
     * @test
     * @return void
     */
    public function MultipleRequired_WhenNotAuthenticated_Unauthorized(): void
    {
        // Arrange
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', RoleAuthorizationController3::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
    public function MultipleRequired_WithoutRoles_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser();
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', RoleAuthorizationController3::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
    public function MultipleRequired_WhenHasOnlyFirstRoles_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser(roles: ['test-role-1']);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', RoleAuthorizationController3::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
    public function MultipleRequired_WhenHasOnlySecondRoles_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser(roles: ['test-role-3']);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', RoleAuthorizationController3::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
    public function MultipleRequired_WhenValid_Success(): void
    {
        // Arrange
        $this->authenticateUser(roles: ['test-role-1', 'test-role-3']);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', RoleAuthorizationController3::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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

    /**
     * @test
     * @return void
     */
    public function MultipleRequiredOnClassAndMethod_WhenNotAuthenticated_Unauthorized(): void
    {
        // Arrange
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', [RoleAuthorizationController4::class, 'onlyRequiresClassRoles'])->middleware([AuthorizationMiddleware::class])->name('test1');
        $router->get('test2', [RoleAuthorizationController4::class, 'requiresClassAndMethodRoles'])->middleware([AuthorizationMiddleware::class])->name('test2');
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->app->make(UrlGenerator::class);

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson($urlGenerator->route('test1')),
            $this->getJson($urlGenerator->route('test2')),
        ];

        // Assert
        $responses[0]->assertUnauthorized();
        $responses[1]->assertUnauthorized();
    }

    /**
     * @test
     * @return void
     */
    public function MultipleRequiredOnClassAndMethod_WithoutRoles_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser();
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', [RoleAuthorizationController4::class, 'onlyRequiresClassRoles'])->middleware([AuthorizationMiddleware::class])->name('test1');
        $router->get('test2', [RoleAuthorizationController4::class, 'requiresClassAndMethodRoles'])->middleware([AuthorizationMiddleware::class])->name('test2');
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->app->make(UrlGenerator::class);

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson($urlGenerator->route('test1')),
            $this->getJson($urlGenerator->route('test2')),
        ];

        // Assert
        $responses[0]->assertForbidden();
        $responses[1]->assertForbidden();
    }

    /**
     * @test
     * @return void
     */
    public function MultipleRequiredOnClassAndMethod_WhenOnlyHasOneClassRoles_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser(roles: [
            'test-role-1',
            'test-role-2'
        ]);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', [RoleAuthorizationController4::class, 'onlyRequiresClassRoles'])->middleware([AuthorizationMiddleware::class])->name('test1');
        $router->get('test2', [RoleAuthorizationController4::class, 'requiresClassAndMethodRoles'])->middleware([AuthorizationMiddleware::class])->name('test2');
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->app->make(UrlGenerator::class);

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson($urlGenerator->route('test1')),
            $this->getJson($urlGenerator->route('test2')),
        ];

        // Assert
        $responses[0]->assertForbidden();
        $responses[1]->assertForbidden();
    }

    /**
     * @test
     * @return void
     */
    public function MultipleRequiredOnClassAndMethod_WhenHasClassRoles_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser(roles: [
            'test-role-1',
            'test-role-3'
        ]);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', [RoleAuthorizationController4::class, 'onlyRequiresClassRoles'])->middleware([AuthorizationMiddleware::class])->name('test1');
        $router->get('test2', [RoleAuthorizationController4::class, 'requiresClassAndMethodRoles'])->middleware([AuthorizationMiddleware::class])->name('test2');
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->app->make(UrlGenerator::class);

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson($urlGenerator->route('test1')),
            $this->getJson($urlGenerator->route('test2')),
        ];

        // Assert
        $responses[0]->assertSuccessful();
        $responses[1]->assertForbidden();
    }

    /**
     * @test
     * @return void
     */
    public function MultipleRequiredOnClassAndMethod_WhenHasClassAndMethodRoles_Success(): void
    {
        // Arrange
        $this->authenticateUser(roles: [
            'test-role-1',
            'test-role-2',
            'test-role-3'
        ]);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', [RoleAuthorizationController4::class, 'onlyRequiresClassRoles'])->middleware([AuthorizationMiddleware::class])->name('test1');
        $router->get('test2', [RoleAuthorizationController4::class, 'requiresClassAndMethodRoles'])->middleware([AuthorizationMiddleware::class])->name('test2');
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->app->make(UrlGenerator::class);

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson($urlGenerator->route('test1')),
            $this->getJson($urlGenerator->route('test2')),
        ];

        // Assert
        $responses[0]->assertSuccessful();
        $responses[1]->assertSuccessful();
    }
}
