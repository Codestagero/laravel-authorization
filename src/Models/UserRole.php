<?php

namespace Codestage\Authorization\Models;

use Carbon\Carbon;
use Codestage\Authorization\Traits\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @template        TUser of Model
 * @property        string                  $role_id
 * @property        int                     $user_id
 * @property        class-string<TUser>     $user_type
 * @property        Carbon                  $created_at
 * @property        Carbon                  $updated_at
 * @property-read   TUser                   $user
 */
class UserRole extends Model
{
    use HasCompositePrimaryKey;

    /**
     * @inheritDoc
     */
    protected $primaryKey = [
        'role_id',
        'user_type',
        'user_id'
    ];

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'role_id',
        'user_id',
        'user_type',
    ];

    /**
     * Get the user entity this payment method belongs to.
     *
     * @return MorphTo
     */
    public function user(): MorphTo
    {
        return $this->morphTo('user');
    }
}
