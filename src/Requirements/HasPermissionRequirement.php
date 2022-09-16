<?php

namespace Codestage\Authorization\Requirements;

use Codestage\Authorization\Attributes\HandledBy;
use Codestage\Authorization\Contracts\{IPermissionEnum, IRequirement};
use Codestage\Authorization\Handlers\HasPermissionRequirementHandler;

#[HandledBy(HasPermissionRequirementHandler::class)]
class HasPermissionRequirement implements IRequirement
{
    /**
     * Check if the user has at least one of the given permissions.
     *
     * @param IPermissionEnum|IPermissionEnum[] $permissions
     */
    public function __construct(public IPermissionEnum|array $permissions)
    {
    }
}
