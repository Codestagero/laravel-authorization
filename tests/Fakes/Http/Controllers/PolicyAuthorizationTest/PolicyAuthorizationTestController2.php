<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\PolicyAuthorizationTest;

use Codestage\Authorization\Attributes\Authorize;
use Codestage\Authorization\Tests\Fakes\Authorization\Policies\PolicyThatRequiresUserProfile;
use Codestage\Authorization\Tests\Fakes\Models\UserProfile;
use Illuminate\Http\Response;

class PolicyAuthorizationTestController2
{
    #[Authorize(PolicyThatRequiresUserProfile::class)]
    public function __invoke(UserProfile $profile): Response
    {
        return new Response(status: 204);
    }
}
