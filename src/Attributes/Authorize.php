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
     * @param class-string|class-string[]|null $policies
     * @param string|string[]|null $roles
     * @param IPermissionEnum|IPermissionEnum[]|null $permissions
     */
    public function __construct(
        public readonly string|array|null $policies = null,
        public readonly string|array|null $roles = null,
        public readonly IPermissionEnum|array|null $permissions = null
    ) {
    }
}
