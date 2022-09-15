<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\SimpleAuthorizationTest;

use Codestage\Authorization\Attributes\{AllowAnonymous, Authorize};
use Illuminate\Support\Facades\Response;

#[Authorize]
class SimpleAuthorizationController3
{
    /**
     * @return \Illuminate\Http\Response
     */
    #[AllowAnonymous]
    public function doesNotRequireAuth(): \Illuminate\Http\Response
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
