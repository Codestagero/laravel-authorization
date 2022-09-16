<?php

namespace Codestage\Authorization\Attributes;

use Attribute;
use Codestage\Authorization\Authorization\Policies\RequireRolePolicy;

#[Attribute(
    Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD
)]
class AuthorizeRole extends Authorize
{
    /**
     * @param string|string[] $roles
     */
    public function __construct(string|array $roles)
    {
        parent::__construct(RequireRolePolicy::class, [
            'roles' => $roles
        ]);
    }
}
