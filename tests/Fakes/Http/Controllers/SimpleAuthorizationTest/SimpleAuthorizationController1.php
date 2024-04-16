<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\SimpleAuthorizationTest;

use Codestage\Authorization\Attributes\Authorize;
use Illuminate\Http\Response;

#[Authorize]
class SimpleAuthorizationController1
{
    /**
     * @return Response
     */
    public function requiresAuth(): Response
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
