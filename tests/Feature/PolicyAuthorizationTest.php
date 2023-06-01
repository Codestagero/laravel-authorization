<?php

namespace Codestage\Authorization\Tests\Feature;

use Carbon\Carbon;
use Codestage\Authorization\Middleware\AuthorizationMiddleware;
use Codestage\Authorization\Tests\Fakes\Http\Controllers\PolicyAuthorizationTest\PolicyAuthorizationTestController1;
use Codestage\Authorization\Tests\Fakes\Http\Controllers\PolicyAuthorizationTest\PolicyAuthorizationTestController2;
use Codestage\Authorization\Tests\Fakes\Models\User;
use Codestage\Authorization\Tests\Fakes\Models\UserProfile;
use Codestage\Authorization\Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;

class PolicyAuthorizationTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function Authorize_WhenClassRequiresPolicyAndNotAuthenticated_Unauthorized(): void
    {
        // Arrange
        Route::get('test1', PolicyAuthorizationTestController1::class)->middleware([AuthorizationMiddleware::class])->name('test1');

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
    public function Authorize_WhenClassRequiresPolicyWhichFails_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser();
        Route::get('test1', PolicyAuthorizationTestController1::class)->middleware([AuthorizationMiddleware::class])->name('test1');
        Carbon::setTestNow(Carbon::parse('2001-07-26T00:00:00Z'));

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
    public function Authorize_WhenClassRequiresPolicyWhichPasses_Success(): void
    {
        // Arrange
        $this->authenticateUser();
        Route::get('test1', PolicyAuthorizationTestController1::class)->middleware([AuthorizationMiddleware::class])->name('test1');
        Carbon::setTestNow(Carbon::parse('2001-12-25T00:00:00Z'));

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1')),
        ];

        // Assert
        $responses[0]->assertSuccessful();
    }
}
