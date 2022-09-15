<?php

namespace Codestage\Authorization\Contracts;

use Closure;
use ReflectionException;

/**
 * @internal
 */
interface ITraitService
{
    /**
     * Check whether the given controller method can be accessed in the current request context.
     *
     * @param class-string $className
     * @param class-string $methodName
     * @throws ReflectionException
     * @return bool
     */
    public function canAccessControllerMethod(string $className, string $methodName): bool;

    /**
     * Check whether the given controller method can be accessed in the current request context.
     *
     * @param Closure $closure
     * @throws ReflectionException
     * @return bool
     */
    public function canAccessClosure(Closure $closure): bool;
}
