<?php

namespace Codestage\Authorization\Contracts;

/**
 * @template TProvides of IPolicy
 */
interface IPolicyProvider
{
    /**
     * Create an instance of the requested policy, binding the given parameters.
     *
     * @param class-string<TProvides> $policy
     * @param array $parameters
     * @return TProvides
     */
    public function make(string $policy, array $parameters): IPolicy;
}
