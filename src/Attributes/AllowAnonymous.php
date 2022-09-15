<?php

namespace Codestage\Authorization\Attributes;

use Attribute;

#[Attribute(
    Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD
)]
class AllowAnonymous
{
    /**
     * This action marks this action does not require an authenticated user to perform.
     *
     * @note This bypasses all authorization statements.
     */
    public function __construct()
    {
    }
}
