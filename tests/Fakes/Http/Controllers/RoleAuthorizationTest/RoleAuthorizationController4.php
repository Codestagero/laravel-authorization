<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\RoleAuthorizationTest;

use Codestage\Authorization\Attributes\AuthorizeRole;
use Illuminate\Support\Facades\Response;

#[AuthorizeRole('test-role-1')]
#[AuthorizeRole('test-role-3')]
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
    #[AuthorizeRole('test-role-2')]
    public function requiresClassAndMethodRoles(): \Illuminate\Http\Response
    {
        return Response::noContent();
    }
}
