<?php

namespace Codestage\Authorization\Contracts;

/**
 * @template TRequirement of IRequirement
 * @template TResource
 */
interface IResourceRequirementHandler
{
    /**
     * Check whether the requirement this class handles is passing.
     *
     * @param TRequirement $requirement
     * @param TResource $resource
     * @return bool
     */
    public function handle(mixed $requirement, mixed $resource): bool;
}
