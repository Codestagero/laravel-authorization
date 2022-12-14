<?php

namespace Codestage\Authorization\Policies;

use Codestage\Authorization\Contracts\{IPolicy, IRequirement};
use Codestage\Authorization\Requirements\HasRoleRequirement;

class RequireRolePolicy implements IPolicy
{
    /**
     * RequireRolePolicy constructor method.
     *
     * @param string|string[] $roles
     */
    public function __construct(public readonly string|array $roles)
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
            new HasRoleRequirement($this->roles)
        ];
    }
}
