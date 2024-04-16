<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\PermissionsAuthorizationTest;

use Codestage\Authorization\Attributes\Authorize;
use Codestage\Authorization\Tests\Fakes\Enums\FakePermission;
use Illuminate\Http\Response;

#[Authorize(permissions: [FakePermission::ExamplePermission1, FakePermission::ExamplePermission3])]
class PermissionAuthorizationController2
{
    /**
     * @return Response
     */
    public function __invoke(): Response
    {
        return new Response(status: 204);
    }
}
