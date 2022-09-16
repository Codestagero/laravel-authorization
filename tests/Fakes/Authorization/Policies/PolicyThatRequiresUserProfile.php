<?php

namespace Codestage\Authorization\Tests\Fakes\Authorization\Policies;

use Codestage\Authorization\Contracts\IPolicy;
use Codestage\Authorization\Contracts\IRequirement;
use Codestage\Authorization\Tests\Fakes\Authorization\Requirement\NotNullRequirement;
use Codestage\Authorization\Tests\Fakes\Models\UserProfile;

class PolicyThatRequiresUserProfile implements IPolicy
{
    public function __construct(public readonly UserProfile $profile)
    {
    }

    /**
     * The list of requirements that need to be fulfilled in order to complete this policy.
     *
     * @return array<int, IRequirement>
     */
    public function requirements(): array
    {
        return [
            new NotNullRequirement($this->profile)
        ];
    }
}
