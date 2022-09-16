<?php

namespace Codestage\Authorization\Tests\Feature;

use Codestage\Authorization\Middleware\CheckAuthorizationMiddleware;
use Codestage\Authorization\Tests\Fakes\Http\Controllers\SimpleAuthorizationTest\{SimpleAuthorizationController1,
    SimpleAuthorizationController2,
    SimpleAuthorizationController3};
use Codestage\Authorization\Tests\TestCase;
use Illuminate\Support\Facades\{Route, URL};
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
        Route::get('test1', [SimpleAuthorizationController1::class, 'requiresAuth'])->middleware([CheckAuthorizationMiddleware::class])->name('test1');
        Route::get('test2', [SimpleAuthorizationController1::class, 'requiresAuthAsWell'])->middleware([CheckAuthorizationMiddleware::class])->name('test2');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
            $this->getJson(URL::route('test2'))
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
        Route::get('test1', [SimpleAuthorizationController1::class, 'requiresAuth'])->middleware([CheckAuthorizationMiddleware::class])->name('test1');
        Route::get('test2', [SimpleAuthorizationController1::class, 'requiresAuthAsWell'])->middleware([CheckAuthorizationMiddleware::class])->name('test2');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
            $this->getJson(URL::route('test2'))
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
        Route::get('test1', [SimpleAuthorizationController2::class, 'doesNotRequireAuth'])->middleware([CheckAuthorizationMiddleware::class])->name('test1');
        Route::get('test2', [SimpleAuthorizationController2::class, 'requiresAuthAsWell'])->middleware([CheckAuthorizationMiddleware::class])->name('test2');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
            $this->getJson(URL::route('test2'))
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
        Route::get('test1', [SimpleAuthorizationController2::class, 'doesNotRequireAuth'])->middleware([CheckAuthorizationMiddleware::class])->name('test1');
        Route::get('test2', [SimpleAuthorizationController2::class, 'requiresAuthAsWell'])->middleware([CheckAuthorizationMiddleware::class])->name('test2');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
            $this->getJson(URL::route('test2'))
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
        Route::get('test1', [SimpleAuthorizationController3::class, 'doesNotRequireAuth'])->middleware([CheckAuthorizationMiddleware::class])->name('test1');
        Route::get('test2', [SimpleAuthorizationController3::class, 'requiresAuthAsWell'])->middleware([CheckAuthorizationMiddleware::class])->name('test2');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
            $this->getJson(URL::route('test2'))
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
        Route::get('test1', [SimpleAuthorizationController3::class, 'doesNotRequireAuth'])->middleware([CheckAuthorizationMiddleware::class])->name('test1');
        Route::get('test2', [SimpleAuthorizationController3::class, 'requiresAuthAsWell'])->middleware([CheckAuthorizationMiddleware::class])->name('test2');

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
            $this->getJson(URL::route('test2'))
        ];

        // Assert
        $responses[0]->assertSuccessful();
        $responses[1]->assertSuccessful();
    }
}
