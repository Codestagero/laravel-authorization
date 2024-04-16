<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\RoleAuthorizationTest;

use Codestage\Authorization\Attributes\Authorize;
use Illuminate\Http\Response;

#[Authorize(roles: 'test-role-1')]
#[Authorize(roles: 'test-role-3')]
class RoleAuthorizationController4
{
    /**
     * @return Response
     */
    public function onlyRequiresClassRoles(): Response
    {
        return new Response(status: 204);
    }

    /**
     * @return Response
     */
    #[Authorize(roles: 'test-role-2')]
    public function requiresClassAndMethodRoles(): Response
    {
        return new Response(status: 204);
    }
}
