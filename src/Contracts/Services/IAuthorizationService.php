<?php

namespace Codestage\Authorization\Contracts\Services;

use Codestage\Authorization\Contracts\{IPolicy, IRequirement};
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * @template TResource
 */
interface IAuthorizationService
{
    /**
     * Check if the current user can access an action behind the given {@link $policy}.
     *
     * @param TResource|null $resource
     * @param IPolicy|class-string $policy
     * @throws BindingResolutionException
     * @return bool
     */
    public function authorizePolicy(mixed $resource, IPolicy|string $policy): bool;

    /**
     * Check if the current user can access an action behind the given {@link $requirements}.
     *
     * @param TResource|null $resource
     * @param iterable<IRequirement> $requirements
     * @return bool
     */
    public function authorizeRequirements(mixed $resource, iterable $requirements): bool;
}
