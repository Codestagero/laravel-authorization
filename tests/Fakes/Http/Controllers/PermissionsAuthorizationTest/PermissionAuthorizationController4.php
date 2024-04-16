<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\PermissionsAuthorizationTest;

use Codestage\Authorization\Attributes\Authorize;
use Codestage\Authorization\Tests\Fakes\Enums\FakePermission;
use Illuminate\Http\Response;

#[Authorize(permissions: FakePermission::ExamplePermission1)]
#[Authorize(permissions: FakePermission::ExamplePermission3)]
class PermissionAuthorizationController4
{
    /**
     * @return Response
     */
    public function onlyRequiresClassPermissions(): Response
    {
        return new Response(status: 204);
    }

    /**
     * @return Response
     */
    #[Authorize(permissions: FakePermission::ExamplePermission2)]
    public function requiresClassAndMethodPermissions(): Response
    {
        return new Response(status: 204);
    }
}
