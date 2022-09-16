<?php

namespace Codestage\Authorization\Contracts\Services;

use Codestage\Authorization\Contracts\IPolicy;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * @internal
 */
interface IPolicyService
{
    /**
     * Run the given policy.
     *
     * @param class-string|IPolicy $policy
     * @throws BindingResolutionException
     * @return bool
     */
    public function runPolicy(string|IPolicy $policy): bool;
}
