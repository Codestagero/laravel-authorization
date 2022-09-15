<?php

namespace Codestage\Authorization\Traits;

use Codestage\Authorization\Contracts\IPermissionEnum;
use Codestage\Authorization\Models\{Role, RolePermission, UserRole};
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;

/**
 * @template        TPermission of \Codestage\Authorization\Contracts\IPermissionEnum
 * @property-read   Collection<Role>        $roles
 * @method          HasManyThrough  hasManyThrough(string $related, string $through, string|null $firstKey = null, string|null $secondKey = null, string|null $localKey = null, string|null $secondLocalKey = null)
 */
trait HasPermissions
{
    /**
     * Get this user's roles.
     *
     * @return HasManyThrough<Role>
     */
    public function roles(): HasManyThrough
    {
        return $this->hasManyThrough(Role::class, UserRole::class, 'user_id', 'id', '', 'role_id');
    }

    /**
     * Get a list of this user's permissions.
     *
     * @return Collection<TPermission>
     */
    public function getPermissions(): Collection
    {
        $permissions = Collection::make();

        /**
         * @var Role $role
         */
        foreach ($this->roles as $role) {
            $permissions = $permissions->concat($role->permissions->map(fn (RolePermission $rolePermission) => $rolePermission->permission));
        }

        return $permissions;
    }

    /**
     * Check if a user has a specific permission.
     *
     * @param TPermission $permission
     * @return bool
     */
    public function hasPermission(IPermissionEnum $permission): bool
    {
        return $this->getPermissions()->contains($permission);
    }

    /**
     * Check if a user has a specific role.
     *
     * @param Role|string $role
     * @return bool
     */
    public function hasRole(Role|string $role): bool
    {
        $requestedKey = match (true) {
            $role instanceof Role => $role->getKey(),
            default => $role
        };

        return $this->roles()->where('key', $requestedKey)->exists();
    }
}
