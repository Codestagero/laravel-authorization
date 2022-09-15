<?php

namespace Codestage\Authorization\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;


/**
 * @template    TPermission of \Codestage\Authorization\Contracts\IPermissionEnum
 *
 * @property    string                      $id
 * @property    string                      $key
 * @property    string                      $name
 * @property    Carbon                      $created_at
 * @property    Carbon                      $updated_at
 * @property    Collection<TPermission>     $permissions
 */
class Role extends Model
{
    /**
     * @inheritDoc
     */
    protected $keyType = 'string';

    /**
     * @inheritDoc
     */
    public $incrementing = false;

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'key',
        'name',
    ];

    /**
     * @inheritDoc
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Role $model): void {
            // Generate a payment id
            $model->id = Str::uuid();
        });
    }

    /**
     * Get this role's permissions.
     *
     * @return HasMany<TPermission>
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }
}
