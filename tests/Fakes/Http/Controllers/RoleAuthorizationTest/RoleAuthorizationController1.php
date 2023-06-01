<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\RoleAuthorizationTest;

use Codestage\Authorization\Attributes\Authorize;
use Illuminate\Support\Facades\Response;

#[Authorize(roles: 'test-role-1')]
class RoleAuthorizationController1
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function __invoke(): \Illuminate\Http\Response
    {
        return Response::noContent();
    }
}
