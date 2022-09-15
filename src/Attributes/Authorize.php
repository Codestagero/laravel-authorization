<?php

namespace Codestage\Authorization\Attributes;

use Attribute;
use Codestage\Authorization\Contracts\IPermissionEnum;

#[Attribute(
    Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD
)]
class Authorize
{
    /**
     * This action requires that the user be authenticated and meet the authorization criteria.
     *
     * @param IPermissionEnum|IPermissionEnum[]|null $permissions
     * @param string|string[]|null $roles
     */
    public function __construct(
        public IPermissionEnum|array|null $permissions = null,
        public string|array|null $roles = null
    ) {
    }
}
