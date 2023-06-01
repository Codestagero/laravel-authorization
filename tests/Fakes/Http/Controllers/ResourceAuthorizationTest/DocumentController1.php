<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\ResourceAuthorizationTest;

use Codestage\Authorization\Attributes\Authorize;
use Codestage\Authorization\Contracts\Services\IAuthorizationService;
use Codestage\Authorization\Tests\Fakes\Authorization\Policies\ActOnDocumentPolicy;
use Codestage\Authorization\Tests\Fakes\Models\Document;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\UnauthorizedException;

class DocumentController1
{
    /**
     * DocumentController1 constructor method.
     *
     * @param IAuthorizationService $_authorizationService
     * @param Guard $_authManager
     */
    public function __construct(
        private readonly IAuthorizationService $_authorizationService,
        private readonly Guard $_authManager
    )
    {
    }

    /**
     * Do nothing.
     *
     * @param int $document
     * @return Response
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws BindingResolutionException
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