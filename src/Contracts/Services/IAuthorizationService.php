<?php

namespace Codestage\Authorization\Contracts\Services;

use Codestage\Authorization\Contracts\IPolicy;
use Codestage\Authorization\Contracts\IRequirement;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * @template TResource
 */
interface IAuthorizationService
{
    /**
     * @param TResource|null $resource
     * @param IPolicy|class-string $policy
     * @return bool
     * @throws BindingResolutionException
     */
    public function authorizePolicy(mixed $resource, IPolicy|string $policy): bool;

    /**
     * @param TResource|null $resource
     * @param iterable<IRequirement> $requirements
     * @return bool
     */
    public function authorizeRequirements(mixed $resource, iterable $requirements): bool;
}