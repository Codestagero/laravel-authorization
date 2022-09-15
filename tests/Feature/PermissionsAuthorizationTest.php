<?php

namespace Codestage\Authorization\Tests\Feature;

use Codestage\Authorization\Middleware\AuthorizeMiddleware;
use Codestage\Authorization\Tests\Fakes\Enums\FakePermission;
use Codestage\Authorization\Tests\Fakes\Http\Controllers\PermissionsAuthorizationTest\{PermissionAuthorizationController1, PermissionAuthorizationController2, PermissionAuthorizationController3, PermissionAuthorizationController4};
use Codestage\Authorization\Tests\TestCase;
use Illuminate\Support\Facades\{Route, URL};
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
        Route::get('test1', PermissionAuthorizationController1::class)->middleware([AuthorizeMiddleware::class])->name('test1');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
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
        Route::get('test1', PermissionAuthorizationController1::class)->middleware([AuthorizeMiddleware::class])->name('test1');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
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
        Route::get('test1', PermissionAuthorizationController1::class)->middleware([AuthorizeMiddleware::class])->name('test1');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
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
        Route::get('test1', PermissionAuthorizationController2::class)->middleware([AuthorizeMiddleware::class])->name('test1');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
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
        Route::get('test1', PermissionAuthorizationController2::class)->middleware([AuthorizeMiddleware::class])->name('test1');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
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
        Route::get('test1', PermissionAuthorizationController2::class)->middleware([AuthorizeMiddleware::class])->name('test1');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
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
        Route::get('test1', PermissionAuthorizationController2::class)->middleware([AuthorizeMiddleware::class])->name('test1');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
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
        Route::get('test1', PermissionAuthorizationController2::class)->middleware([AuthorizeMiddleware::class])->name('test1');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
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
        Route::get('test1', PermissionAuthorizationController2::class)->middleware([AuthorizeMiddleware::class])->name('test1');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
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
        Route::get('test1', PermissionAuthorizationController3::class)->middleware([AuthorizeMiddleware::class])->name('test1');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
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
        Route::get('test1', PermissionAuthorizationController3::class)->middleware([AuthorizeMiddleware::class])->name('test1');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
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
        Route::get('test1', PermissionAuthorizationController3::class)->middleware([AuthorizeMiddleware::class])->name('test1');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
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
        Route::get('test1', PermissionAuthorizationController3::class)->middleware([AuthorizeMiddleware::class])->name('test1');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
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
        Route::get('test1', PermissionAuthorizationController3::class)->middleware([AuthorizeMiddleware::class])->name('test1');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
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
        Route::get('test1', [PermissionAuthorizationController4::class, 'onlyRequiresClassPermissions'])->middleware([AuthorizeMiddleware::class])->name('test1');
        Route::get('test2', [PermissionAuthorizationController4::class, 'requiresClassAndMethodPermissions'])->middleware([AuthorizeMiddleware::class])->name('test2');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
            $this->getJson(URL::route('test2')),
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
        Route::get('test1', [PermissionAuthorizationController4::class, 'onlyRequiresClassPermissions'])->middleware([AuthorizeMiddleware::class])->name('test1');
        Route::get('test2', [PermissionAuthorizationController4::class, 'requiresClassAndMethodPermissions'])->middleware([AuthorizeMiddleware::class])->name('test2');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
            $this->getJson(URL::route('test2')),
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
        Route::get('test1', [PermissionAuthorizationController4::class, 'onlyRequiresClassPermissions'])->middleware([AuthorizeMiddleware::class])->name('test1');
        Route::get('test2', [PermissionAuthorizationController4::class, 'requiresClassAndMethodPermissions'])->middleware([AuthorizeMiddleware::class])->name('test2');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
            $this->getJson(URL::route('test2')),
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
        Route::get('test1', [PermissionAuthorizationController4::class, 'onlyRequiresClassPermissions'])->middleware([AuthorizeMiddleware::class])->name('test1');
        Route::get('test2', [PermissionAuthorizationController4::class, 'requiresClassAndMethodPermissions'])->middleware([AuthorizeMiddleware::class])->name('test2');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
            $this->getJson(URL::route('test2')),
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
        Route::get('test1', [PermissionAuthorizationController4::class, 'onlyRequiresClassPermissions'])->middleware([AuthorizeMiddleware::class])->name('test1');
        Route::get('test2', [PermissionAuthorizationController4::class, 'requiresClassAndMethodPermissions'])->middleware([AuthorizeMiddleware::class])->name('test2');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
            $this->getJson(URL::route('test2')),
        ];

        // Assert
        $responses[0]->assertSuccessful();
        $responses[1]->assertSuccessful();
    }
}
