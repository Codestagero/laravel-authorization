<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\SimpleAuthorizationTest;

use Codestage\Authorization\Attributes\{AllowAnonymous, Authorize};
use Illuminate\Http\Response;

#[Authorize]
class SimpleAuthorizationController3
{
    /**
     * @return Response
     */
    #[AllowAnonymous]
    public function doesNotRequireAuth(): Response
    {
        return new Response(status: 204);
    }

    /**
     * @return Response
     */
    public function requiresAuthAsWell(): Response
    {
        return new Response(status: 204);
    }
}
