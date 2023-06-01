<?php

namespace Codestage\Authorization\Handlers;

use Codestage\Authorization\Contracts\{IPermissionEnum, IRequirement, IRequirementHandler};
use Codestage\Authorization\Requirements\HasPermissionRequirement;
use Codestage\Authorization\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Guard as AuthManager;
use Illuminate\Support\Collection;

/**
 * @implements IRequirementHandler<HasPermissionRequirement>
 */
class HasPermissionRequirementHandler implements IRequirementHandler
{
    /**
     * HasPermissionHandler constructor method.
     *
     * @param AuthManager $authManager
     */
    public function __construct(
        private readonly AuthManager $_authManager
    ) {
    }

    /**
     * Check whether the requirement this class handles is passing.
     *
     * @param HasPermissionRequirement $requirement
     * @return bool
     */
    public function handle(IRequirement $requirement): bool
    {
        /** @var HasPermissions $user */
        $user = $this->_authManager->user();

        // If there is no current user, the requirement fails
        if (!$user) {
            return false;
        }

        // The requirement passes if there is at least one permission that this user indeed has
        return (new Collection($requirement->permissions))->some(fn (IPermissionEnum $permission) => $user->hasPermission($permission));
    }
}
