<?php

namespace Codestage\Authorization\Requirements;

use Codestage\Authorization\Attributes\HandledBy;
use Codestage\Authorization\Contracts\IRequirement;
use Codestage\Authorization\Handlers\HasRoleRequirementHandler;

#[HandledBy(HasRoleRequirementHandler::class)]
class HasRoleRequirement implements IRequirement
{
    /**
     * Check if the user has at least one of the given roles.
     *
     * @param string|string[] $roles
     */
    public function __construct(public string|array $roles)
    {
    }
}
