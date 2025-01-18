<?php

namespace Codestage\Authorization\Contracts;

/**
 * @template TResource
 */
interface IResourcePolicy
{
    /**
     * The list of requirements that need to be fulfilled in order to complete this policy.
     *
     * @param TResource $resource
     * @return array<int, IRequirement>
     */
    public function requirements(mixed $resource): array;
}
