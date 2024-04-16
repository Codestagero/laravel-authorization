<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\SimpleAuthorizationTest;

use Codestage\Authorization\Attributes\Authorize;
use Illuminate\Http\Response;

class SimpleAuthorizationController2
{
    /**
     * @return Response
     */
    public function doesNotRequireAuth(): Response
    {
        return new Response(status: 204);
    }

    /**
     * @return Response
     */
    #[Authorize]
    public function requiresAuthAsWell(): Response
    {
        return new Response(status: 204);
    }
}
