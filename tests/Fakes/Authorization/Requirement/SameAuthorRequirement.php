<?php

namespace Codestage\Authorization\Tests\Fakes\Authorization\Requirement;

use Codestage\Authorization\Attributes\HandledBy;
use Codestage\Authorization\Contracts\IRequirement;
use Codestage\Authorization\Tests\Fakes\Authorization\Handlers\SameAuthorRequirementHandler;

#[HandledBy(SameAuthorRequirementHandler::class)]
class SameAuthorRequirement implements IRequirement
{
}
