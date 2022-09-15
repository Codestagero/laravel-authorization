<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\PermissionsAuthorizationTest;

use Codestage\Authorization\Attributes\Authorize;
use Codestage\Authorization\Tests\Fakes\Enums\FakePermission;
use Illuminate\Support\Facades\Response;

#[Authorize(FakePermission::ExamplePermission1)]
class PermissionAuthorizationController1
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function __invoke(): \Illuminate\Http\Response
    {
        return Response::noContent();
    }
}
