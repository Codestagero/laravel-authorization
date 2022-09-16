<?php

namespace Codestage\Authorization\Tests\Fakes\Authorization\Policies;

use Carbon\Carbon;
use Codestage\Authorization\Contracts\IPolicy;
use Codestage\Authorization\Tests\Fakes\Authorization\Requirement\DateRequirement;

class RequireDateChristmasPolicy implements IPolicy
{
    /**
     * @inheritDoc
     */
    public function requirements(): array
    {
        $christmas = Carbon::today()->setDay(25)->setMonth(12);

        return [
            new DateRequirement($christmas)
        ];
    }
}
