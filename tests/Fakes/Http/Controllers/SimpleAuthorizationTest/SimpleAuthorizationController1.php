<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\SimpleAuthorizationTest;

use Codestage\Authorization\Attributes\Authorize;
use Illuminate\Support\Facades\Response;

#[Authorize]
class SimpleAuthorizationController1
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function requiresAuth(): \Illuminate\Http\Response
    {
        return Response::noContent();
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function requiresAuthAsWell(): \Illuminate\Http\Response
    {
        return Response::noContent();
    }
}
