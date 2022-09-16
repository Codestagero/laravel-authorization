<?php

namespace Codestage\Authorization\Contracts;

/**
 * @template TRequirement of IRequirement
 */
interface IRequirementHandler
{
    /**
     * Check whether the requirement this class handles is passing.
     *
     * @param TRequirement $requirement
     * @return bool
     */
    public function handle(IRequirement $requirement): bool;
}
