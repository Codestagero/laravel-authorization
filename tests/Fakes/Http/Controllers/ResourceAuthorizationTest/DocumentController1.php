<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\ResourceAuthorizationTest;

use Codestage\Authorization\Contracts\Services\IAuthorizationService;
use Codestage\Authorization\Tests\Fakes\Authorization\Policies\ActOnDocumentPolicy;
use Codestage\Authorization\Tests\Fakes\Models\Document;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Guard as AuthManager;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Response;

class DocumentController1
{
    /**
     * DocumentController1 constructor method.
     *
     * @param IAuthorizationService $_authorizationService
     * @param AuthManager $_authManager
     */
    public function __construct(
        private readonly IAuthorizationService $_authorizationService,
        private readonly AuthManager $_authManager
    ) {
    }

    /**
     * Do nothing.
     *
     * @param int $document
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws BindingResolutionException
     * @return Response
     */
    public function __invoke(int $document): Response
    {
        $document = Document::query()->findOrFail($document);

        if ($this->_authorizationService->authorizePolicy($document, ActOnDocumentPolicy::class)) {
            return new Response();
        } else if ($this->_authManager->check()) {
            throw new AuthorizationException();
        } else {
            throw new AuthenticationException();
        }
    }
}
