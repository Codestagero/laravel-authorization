<?php

namespace Codestage\Authorization\Contracts\Services;

use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use ReflectionException;

/**
 * @internal
 */
interface IAuthorizationService
{
    /**
     * Check whether the given controller method can be accessed in the current request context.
     *
     * @param class-string $className
     * @param class-string $methodName
     * @throws BindingResolutionException
     * @throws ReflectionException
     * @return bool
     */
    public function canAccessControllerMethod(string $className, string $methodName): bool;

    /**
     * Check whether the given controller method can be accessed in the current request context.
     *
     * @param Closure $closure
     * @throws BindingResolutionException
     * @throws ReflectionException
     * @return bool
     */
    public function canAccessClosure(Closure $closure): bool;
}
