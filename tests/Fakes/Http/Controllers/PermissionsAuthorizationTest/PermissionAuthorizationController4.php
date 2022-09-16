<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\PermissionsAuthorizationTest;

use Codestage\Authorization\Attributes\AuthorizePermission;
use Codestage\Authorization\Tests\Fakes\Enums\FakePermission;
use Illuminate\Support\Facades\Response;

#[AuthorizePermission(FakePermission::ExamplePermission1)]
#[AuthorizePermission(FakePermission::ExamplePermission3)]
class PermissionAuthorizationController4
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function onlyRequiresClassPermissions(): \Illuminate\Http\Response
    {
        return Response::noContent();
    }

    /**
     * @return \Illuminate\Http\Response
     */
    #[AuthorizePermission(FakePermission::ExamplePermission2)]
    public function requiresClassAndMethodPermissions(): \Illuminate\Http\Response
    {
        return Response::noContent();
    }
}
