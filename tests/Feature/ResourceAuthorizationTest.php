<?php

namespace Codestage\Authorization\Tests\Feature;

use Carbon\Carbon;
use Codestage\Authorization\Middleware\AuthorizationMiddleware;
use Codestage\Authorization\Tests\Fakes\Http\Controllers\PolicyAuthorizationTest\PolicyAuthorizationTestController1;
use Codestage\Authorization\Tests\Fakes\Http\Controllers\ResourceAuthorizationTest\DocumentController1;
use Codestage\Authorization\Tests\Fakes\Models\Document;
use Codestage\Authorization\Tests\Fakes\Models\User;
use Codestage\Authorization\Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;

class ResourceAuthorizationTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function Authorize_WhenNotAuthenticated_Unauthorized(): void
    {
        // Arrange
        Route::get('test1/{document}', DocumentController1::class)
            ->middleware([AuthorizationMiddleware::class])
            ->name('test1');
        $user = User::query()->create();
        $document = Document::query()->create([
            'user_id' => $user->getKey()
        ]);

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1', $document)),
        ];

        // Assert
        $responses[0]->assertUnauthorized();
    }

    /**
     * @test
     * @return void
     */
    public function Authorize_WhenFails_Forbidden(): void
    {
        // Arrange
        $this->authenticateUser();
        Route::get('test1/{document}', DocumentController1::class)
            ->middleware([AuthorizationMiddleware::class])
            ->name('test1');
        $user = User::query()->create();
        $document = Document::query()->create([
            'user_id' => $user->getKey()
        ]);

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1', $document)),
        ];

        // Assert
        $responses[0]->assertForbidden();
    }

    /**
     * @test
     * @return void
     */
    public function Authorize_WhenPasses_Success(): void
    {
        // Arrange
        Route::get('test1/{document}', DocumentController1::class)
            ->middleware([AuthorizationMiddleware::class])
            ->name('test1');
        $user = $this->authenticateUser();
        $document = Document::query()->create([
            'user_id' => $user->getKey()
        ]);

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson(URL::route('test1', $document)),
        ];

        // Assert
        $responses[0]->assertSuccessful();
    }
}
