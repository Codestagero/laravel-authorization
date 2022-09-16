<?php

namespace Codestage\Authorization\Tests\Fakes\Authorization\Handlers;

use Codestage\Authorization\Contracts\IRequirement;
use Codestage\Authorization\Contracts\IRequirementHandler;
use Codestage\Authorization\Tests\Fakes\Authorization\Requirement\NotNullRequirement;

class NotNullRequirementHandler implements IRequirementHandler
{

    /**
     * Check whether the requirement this class handles is passing.
     *
     * @param NotNullRequirement $requirement
     * @return bool
     */
    public function handle(IRequirement $requirement): bool
    {
        return $requirement->value !== null;
    }
}
