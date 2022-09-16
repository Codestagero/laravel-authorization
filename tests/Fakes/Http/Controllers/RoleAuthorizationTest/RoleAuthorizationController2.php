<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\RoleAuthorizationTest;

use Codestage\Authorization\Attributes\AuthorizeRole;
use Illuminate\Support\Facades\Response;

#[AuthorizeRole(['test-role-1', 'test-role-3'])]
class RoleAuthorizationController2
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function __invoke(): \Illuminate\Http\Response
    {
        return Response::noContent();
    }
}
