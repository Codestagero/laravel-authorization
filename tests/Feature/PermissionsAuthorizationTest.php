<?php

namespace Codestage\Authorization\Tests\Feature;

use Codestage\Authorization\Middleware\AuthorizationMiddleware;
use Codestage\Authorization\Tests\Fakes\Enums\FakePermission;
use Codestage\Authorization\Tests\Fakes\Http\Controllers\PermissionsAuthorizationTest\{
    PermissionAuthorizationController1,
    PermissionAuthorizationController2,
    PermissionAuthorizationController3,
    PermissionAuthorizationController4};
use Codestage\Authorization\Tests\TestCase;
use Illuminate\Contracts\Routing\{Registrar, UrlGenerator};
use Illuminate\Testing\TestResponse;

/**
 * @coversNothing
 */
class PermissionsAuthorizationTest extends TestCase
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
        $router->get('test1', PermissionAuthorizationController1::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
    public function OnlyClassGuard_WhenClassRequiresAuthorizationAndDoesNotHavePermission_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser();
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', PermissionAuthorizationController1::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
        $this->authenticateUser([FakePermission::ExamplePermission1]);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', PermissionAuthorizationController1::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
        $router->get('test1', PermissionAuthorizationController2::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
    public function MultipleOptional_WhenClassRequiresAuthorizationAndDoesNotHavePermissions_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser();
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', PermissionAuthorizationController2::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
    public function MultipleOptional_WhenClassRequiresAuthorizationAndHasNoValidPermission_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser([FakePermission::ExamplePermission2]);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', PermissionAuthorizationController2::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
    public function MultipleOptional_WhenClassRequiresAuthorizationAndHasOnePermission_Success(): void
    {
        // Arrange
        $this->authenticateUser([FakePermission::ExamplePermission1]);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', PermissionAuthorizationController2::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
    public function MultipleOptional_WhenClassRequiresAuthorizationAndHasOtherPermission_Success(): void
    {
        // Arrange
        $this->authenticateUser([FakePermission::ExamplePermission3]);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', PermissionAuthorizationController2::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
    public function MultipleOptional_WhenClassRequiresAuthorizationAndHasBothPermissions_Success(): void
    {
        // Arrange
        $this->authenticateUser([FakePermission::ExamplePermission1, FakePermission::ExamplePermission3]);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', PermissionAuthorizationController2::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
        $router->get('test1', PermissionAuthorizationController3::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
    public function MultipleRequired_WithoutPermissions_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser();
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', PermissionAuthorizationController3::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
    public function MultipleRequired_WhenHasOnlyFirstPermission_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser([FakePermission::ExamplePermission1]);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', PermissionAuthorizationController3::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
    public function MultipleRequired_WhenHasOnlySecondPermission_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser([FakePermission::ExamplePermission3]);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', PermissionAuthorizationController3::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
        $this->authenticateUser([FakePermission::ExamplePermission1, FakePermission::ExamplePermission3]);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', PermissionAuthorizationController3::class)->middleware([AuthorizationMiddleware::class])->name('test1');
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
        $router->get('test1', [PermissionAuthorizationController4::class, 'onlyRequiresClassPermissions'])->middleware([AuthorizationMiddleware::class])->name('test1');
        $router->get('test2', [PermissionAuthorizationController4::class, 'requiresClassAndMethodPermissions'])->middleware([AuthorizationMiddleware::class])->name('test2');
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
    public function MultipleRequiredOnClassAndMethod_WithoutPermissions_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser();
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', [PermissionAuthorizationController4::class, 'onlyRequiresClassPermissions'])->middleware([AuthorizationMiddleware::class])->name('test1');
        $router->get('test2', [PermissionAuthorizationController4::class, 'requiresClassAndMethodPermissions'])->middleware([AuthorizationMiddleware::class])->name('test2');
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
    public function MultipleRequiredOnClassAndMethod_WhenOnlyHasOneClassPermission_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser([
            FakePermission::ExamplePermission1,
            FakePermission::ExamplePermission2
        ]);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', [PermissionAuthorizationController4::class, 'onlyRequiresClassPermissions'])->middleware([AuthorizationMiddleware::class])->name('test1');
        $router->get('test2', [PermissionAuthorizationController4::class, 'requiresClassAndMethodPermissions'])->middleware([AuthorizationMiddleware::class])->name('test2');
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
    public function MultipleRequiredOnClassAndMethod_WhenHasClassPermissions_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser([
            FakePermission::ExamplePermission1,
            FakePermission::ExamplePermission3
        ]);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', [PermissionAuthorizationController4::class, 'onlyRequiresClassPermissions'])->middleware([AuthorizationMiddleware::class])->name('test1');
        $router->get('test2', [PermissionAuthorizationController4::class, 'requiresClassAndMethodPermissions'])->middleware([AuthorizationMiddleware::class])->name('test2');
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
    public function MultipleRequiredOnClassAndMethod_WhenHasClassAndMethodPermissions_Success(): void
    {
        // Arrange
        $this->authenticateUser([
            FakePermission::ExamplePermission1,
            FakePermission::ExamplePermission2,
            FakePermission::ExamplePermission3
        ]);
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1', [PermissionAuthorizationController4::class, 'onlyRequiresClassPermissions'])->middleware([AuthorizationMiddleware::class])->name('test1');
        $router->get('test2', [PermissionAuthorizationController4::class, 'requiresClassAndMethodPermissions'])->middleware([AuthorizationMiddleware::class])->name('test2');
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
