<?php

namespace Codestage\Authorization\Contracts\Services;

use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * @internal
 */
interface IPolicyService
{
    /**
     * Run the given policy.
     *
     * @param class-string $policy
     * @throws BindingResolutionException
     * @return bool
     */
    public function runPolicy(string $policy): bool;
}
