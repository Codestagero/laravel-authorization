<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\SimpleAuthorizationTest;

use Codestage\Authorization\Attributes\Authorize;
use Illuminate\Support\Facades\Response;

class SimpleAuthorizationController2
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function doesNotRequireAuth(): \Illuminate\Http\Response
    {
        return Response::noContent();
    }

    /**
     * @return \Illuminate\Http\Response
     */
    #[Authorize]
    public function requiresAuthAsWell(): \Illuminate\Http\Response
    {
        return Response::noContent();
    }
}
