<?php

namespace Codestage\Authorization\Models;

use Carbon\Carbon;
use Codestage\Authorization\Contracts\IPermissionEnum;
use Codestage\Authorization\Traits\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * @template    TPermission of IPermissionEnum
 *
 * @property    string          $role_id
 * @property    TPermission     $permission
 * @property    Carbon          $created_at
 * @property    Carbon          $updated_at
 */
class RolePermission extends Model
{
    use HasCompositePrimaryKey;

    /**
     * @inheritDoc
     */
    protected $primaryKey = [
        'role_id',
        'permission'
    ];

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'role_id',
        'permission'
    ];

    /**
     * Interact with this entity's actual permission.
     *
     * @return Attribute
     */
    protected function permission(): Attribute
    {
        /** @var class-string<TPermission> $permissionsEnum */
        $permissionsEnum = Config::get('authorization.permissions_enum');

        return new Attribute(
            get: fn (string|int $permission): IPermissionEnum => $permissionsEnum::from($permission),
            set: fn (IPermissionEnum $permission): string|int => $permission->value,
        );
    }
}
