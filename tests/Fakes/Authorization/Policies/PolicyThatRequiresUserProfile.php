<?php

namespace Codestage\Authorization\Tests\Fakes\Authorization\Policies;

use Codestage\Authorization\Contracts\{IPolicy, IRequirement};

class PolicyThatRequiresUserProfile implements IPolicy
{
    /**
     * The list of requirements that need to be fulfilled in order to complete this policy.
     *
     * @return array<int, IRequirement>
     */
    public function requirements(): array
    {
        return [
        ];
    }
}
