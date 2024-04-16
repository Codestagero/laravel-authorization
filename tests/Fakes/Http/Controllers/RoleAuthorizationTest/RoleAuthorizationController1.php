<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\RoleAuthorizationTest;

use Codestage\Authorization\Attributes\Authorize;
use Illuminate\Http\Response;

#[Authorize(roles: 'test-role-1')]
class RoleAuthorizationController1
{
    /**
     * @return Response
     */
    public function __invoke(): Response
    {
        return new Response(status: 204);
    }
}
