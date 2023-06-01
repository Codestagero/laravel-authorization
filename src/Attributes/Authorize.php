<?php

namespace Codestage\Authorization\Attributes;

use Attribute;

#[Attribute(
    Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD
)]
class Authorize
{
    /**
     * This action requires that the user be authenticated and meet the authorization criteria.
     *
     * @param class-string|class-string[]|null $policies
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        public readonly string|array|null $policies = null,
        /** @deprecated You should no longer try to pass arguments directly */
        public readonly array $parameters = []
    ) {
    }
}
