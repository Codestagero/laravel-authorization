<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\PolicyAuthorizationTest;

use Codestage\Authorization\Attributes\Authorize;
use Codestage\Authorization\Tests\Fakes\Authorization\Policies\RequireDateChristmasPolicy;
use Illuminate\Support\Facades\Response;

#[Authorize(RequireDateChristmasPolicy::class)]
class PolicyAuthorizationTestController1
{
    public function __invoke()
    {
        return Response::noContent();
    }
}
