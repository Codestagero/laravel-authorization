<?php

namespace Codestage\Authorization\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class HandledBy
{
    /**
     * @param class-string|array<number, class-string> $handler
     */
    public function __construct(public string|array $handler)
    {
    }
}
