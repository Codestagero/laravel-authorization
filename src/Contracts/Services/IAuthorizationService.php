<?php

namespace Codestage\Authorization\Contracts\Services;

use Codestage\Authorization\Contracts\{IPolicy, IRequirement, IResourcePolicy};
use Illuminate\Contracts\Container\BindingResolutionException;
use InvalidArgumentException;

/**
 * @template TResource
 */
interface IAuthorizationService
{
    /**
     * Check if the current user can access an action behind the given {@link $policy}.
     *
     * @param TResource|null $resource
     * @param IPolicy|IResourcePolicy<TResource>|class-string $policy
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     * @return bool
     */
    public function authorizePolicy(mixed $resource, IPolicy|IResourcePolicy|string $policy): bool;

    /**
     * Check if the current user can access an action behind the given {@link $requirements}.
     *
     * @param TResource|null $resource
     * @param iterable<IRequirement> $requirements
     * @return bool
     */
    public function authorizeRequirements(mixed $resource, iterable $requirements): bool;
}
