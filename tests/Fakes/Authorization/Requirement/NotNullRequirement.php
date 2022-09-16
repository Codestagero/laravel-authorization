<?php

namespace Codestage\Authorization\Tests\Fakes\Authorization\Requirement;

use Codestage\Authorization\Attributes\HandledBy;
use Codestage\Authorization\Contracts\IRequirement;
use Codestage\Authorization\Tests\Fakes\Authorization\Handlers\NotNullRequirementHandler;

#[HandledBy(NotNullRequirementHandler::class)]
class NotNullRequirement implements IRequirement
{
    public function __construct(public readonly mixed $value)
    {
    }
}
