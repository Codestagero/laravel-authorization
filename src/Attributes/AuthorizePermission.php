<?php

namespace Codestage\Authorization\Attributes;

use Attribute;
use Codestage\Authorization\Authorization\Policies\RequirePermissionPolicy;
use Codestage\Authorization\Contracts\IPermissionEnum;

#[Attribute(
    Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD
)]
class AuthorizePermission extends Authorize
{
    /**
     * @param IPermissionEnum|IPermissionEnum[] $permissions
     */
    public function __construct(IPermissionEnum|array $permissions)
    {
        parent::__construct(RequirePermissionPolicy::class, [
            'permissions' => $permissions
        ]);
    }
}
