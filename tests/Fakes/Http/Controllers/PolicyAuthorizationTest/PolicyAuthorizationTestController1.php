<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\PolicyAuthorizationTest;

use Codestage\Authorization\Attributes\Authorize;
use Codestage\Authorization\Tests\Fakes\Authorization\Policies\RequireDateChristmasPolicy;
use Illuminate\Http\Response;

#[Authorize(RequireDateChristmasPolicy::class)]
class PolicyAuthorizationTestController1
{
    public function __invoke(): Response
    {
        return new Response(status: 204);
    }
}
