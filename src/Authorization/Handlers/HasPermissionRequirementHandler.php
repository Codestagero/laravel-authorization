<?php

namespace Codestage\Authorization\Authorization\Handlers;

use Codestage\Authorization\Authorization\Requirements\HasPermissionRequirement;
use Codestage\Authorization\Contracts\{IPermissionEnum, IRequirement, IRequirementHandler};
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
    public function __construct(private readonly AuthManager $authManager)
    {
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
        $user = $this->authManager->user();

        // If there is no current user, the requirement fails
        if (!$user) {
            return false;
        }

        // The requirement passes if there is at least one permission that this user indeed has
        return (new Collection($requirement->permissions))->some(fn (IPermissionEnum $permission) => $user->hasPermission($permission));
    }
}
