<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\PermissionsAuthorizationTest;

use Codestage\Authorization\Attributes\Authorize;
use Codestage\Authorization\Tests\Fakes\Enums\FakePermission;
use Illuminate\Http\Response;

#[Authorize(permissions: FakePermission::ExamplePermission1)]
#[Authorize(permissions: FakePermission::ExamplePermission3)]
class PermissionAuthorizationController3
{
    /**
     * @return Response
     */
    public function __invoke(): Response
    {
        return new Response(status: 204);
    }
}
