<?php

namespace Codestage\Authorization\Handlers;

use Codestage\Authorization\Contracts\{IRequirement, IRequirementHandler};
use Codestage\Authorization\Requirements\HasRoleRequirement;
use Codestage\Authorization\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Factory as AuthManager;
use Illuminate\Support\Collection;

/**
 * @implements IRequirementHandler<HasRoleRequirement>
 */
readonly class HasRoleRequirementHandler implements IRequirementHandler
{
    /**
     * HasRoleRequirementHandler constructor method.
     */
    public function __construct(
        private AuthManager $_authManager
    ) {
    }

    /**
     * Check whether the requirement this class handles is passing.
     *
     * @param HasRoleRequirement $requirement
     * @return bool
     */
    public function handle(IRequirement $requirement): bool
    {
        /** @var HasPermissions $user */
        $user = $this->_authManager->guard()->user();

        // If there is no current user, the requirement fails
        if (!$user) {
            return false;
        }

        // The requirement passes if there is at least one role that this user indeed has
        return (new Collection($requirement->roles))->some(fn (string $role) => $user->hasRole($role));
    }
}
