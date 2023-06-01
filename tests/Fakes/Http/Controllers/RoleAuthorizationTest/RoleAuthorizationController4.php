<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\RoleAuthorizationTest;

use Codestage\Authorization\Attributes\Authorize;
use Illuminate\Support\Facades\Response;

#[Authorize(roles: 'test-role-1')]
#[Authorize(roles: 'test-role-3')]
class RoleAuthorizationController4
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function onlyRequiresClassRoles(): \Illuminate\Http\Response
    {
        return Response::noContent();
    }

    /**
     * @return \Illuminate\Http\Response
     */
    #[Authorize(roles: 'test-role-2')]
    public function requiresClassAndMethodRoles(): \Illuminate\Http\Response
    {
        return Response::noContent();
    }
}
