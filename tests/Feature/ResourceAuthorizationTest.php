<?php

namespace Codestage\Authorization\Tests\Feature;

use Codestage\Authorization\Middleware\AuthorizationMiddleware;
use Codestage\Authorization\Tests\Fakes\Http\Controllers\ResourceAuthorizationTest\DocumentController1;
use Codestage\Authorization\Tests\Fakes\Models\{Document, User};
use Codestage\Authorization\Tests\TestCase;
use Illuminate\Contracts\Routing\{Registrar, UrlGenerator};
use Illuminate\Testing\TestResponse;

/**
 * @coversNothing
 */
class ResourceAuthorizationTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function Authorize_WhenNotAuthenticated_Unauthorized(): void
    {
        // Arrange
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1/{document}', DocumentController1::class)
            ->middleware([AuthorizationMiddleware::class])
            ->name('test1');
        $user = User::query()->create();
        $document = Document::query()->create([
            'user_id' => $user->getKey()
        ]);
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->app->make(UrlGenerator::class);

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson($urlGenerator->route('test1', $document)),
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
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1/{document}', DocumentController1::class)
            ->middleware([AuthorizationMiddleware::class])
            ->name('test1');
        $user = User::query()->create();
        $document = Document::query()->create([
            'user_id' => $user->getKey()
        ]);
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->app->make(UrlGenerator::class);

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson($urlGenerator->route('test1', $document)),
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
        /** @var Registrar $router */
        $router = $this->app->make(Registrar::class);
        $router->get('test1/{document}', DocumentController1::class)
            ->middleware([AuthorizationMiddleware::class])
            ->name('test1');
        $user = $this->authenticateUser();
        $document = Document::query()->create([
            'user_id' => $user->getKey()
        ]);
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->app->make(UrlGenerator::class);

        // Act
        /** @var TestResponse[] $responses */
        $responses = [
            $this->getJson($urlGenerator->route('test1', $document)),
        ];

        // Assert
        $responses[0]->assertSuccessful();
    }
}
