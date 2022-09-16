<?php

namespace Codestage\Authorization\Contracts;

interface IPolicy
{
    /**
     * The list of requirements that need to be fulfilled in order to complete this policy.
     *
     * @return array<int, IRequirement>
     */
    public function requirements(): array;
}
