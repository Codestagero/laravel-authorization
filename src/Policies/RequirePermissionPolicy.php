<?php

namespace Codestage\Authorization\Policies;

use Codestage\Authorization\Contracts\{IPermissionEnum, IPolicy, IRequirement};
use Codestage\Authorization\Requirements\HasPermissionRequirement;

class RequirePermissionPolicy implements IPolicy
{
    /**
     * RequirePermissionPolicy constructor method.
     *
     * @param IPermissionEnum|array $permissions
     */
    public function __construct(public readonly IPermissionEnum|array $permissions)
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
            new HasPermissionRequirement($this->permissions)
        ];
    }
}
