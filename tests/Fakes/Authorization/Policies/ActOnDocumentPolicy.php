<?php

namespace Codestage\Authorization\Tests\Fakes\Authorization\Policies;

use Codestage\Authorization\Contracts\IPolicy;
use Codestage\Authorization\Tests\Fakes\Authorization\Requirement\SameAuthorRequirement;

class ActOnDocumentPolicy implements IPolicy
{
    /**
     * @inheritDoc
     */
    public function requirements(): array
    {
        return [
            new SameAuthorRequirement()
        ];
    }
}
