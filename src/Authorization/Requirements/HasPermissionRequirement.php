<?php

namespace Codestage\Authorization\Authorization\Requirements;

use Codestage\Authorization\Attributes\HandledBy;
use Codestage\Authorization\Authorization\Handlers\HasPermissionRequirementHandler;
use Codestage\Authorization\Contracts\IPermissionEnum;
use Codestage\Authorization\Contracts\IRequirement;

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
