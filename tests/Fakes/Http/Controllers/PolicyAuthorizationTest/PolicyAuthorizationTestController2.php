<?php

namespace Codestage\Authorization\Tests\Fakes\Http\Controllers\PolicyAuthorizationTest;

use Codestage\Authorization\Attributes\Authorize;
use Codestage\Authorization\Tests\Fakes\Authorization\Policies\PolicyThatRequiresUserProfile;
use Codestage\Authorization\Tests\Fakes\Models\UserProfile;
use Illuminate\Support\Facades\Response;

class PolicyAuthorizationTestController2
{
    #[Authorize(PolicyThatRequiresUserProfile::class)]
    public function __invoke(UserProfile $profile)
    {
        return Response::noContent();
    }
}
