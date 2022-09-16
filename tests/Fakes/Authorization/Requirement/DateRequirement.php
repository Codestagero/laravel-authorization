<?php

namespace Codestage\Authorization\Tests\Fakes\Authorization\Requirement;

use Carbon\Carbon;
use Codestage\Authorization\Attributes\HandledBy;
use Codestage\Authorization\Contracts\IRequirement;
use Codestage\Authorization\Tests\Fakes\Authorization\Handlers\DateRequirementHandler;

#[HandledBy(DateRequirementHandler::class)]
class DateRequirement implements IRequirement
{
    public function __construct(public Carbon $date)
    {
    }
}
