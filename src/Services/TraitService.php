<?php

namespace Codestage\Authorization\Services;

use Closure;
use Codestage\Authorization\Attributes\AllowAnonymous;
use Codestage\Authorization\Attributes\Authorize;
use Codestage\Authorization\Contracts\IPermissionEnum;
use Codestage\Authorization\Contracts\ITraitService;
use Codestage\Authorization\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Guard as AuthManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;

/**
 * @internal
 */
class TraitService implements ITraitService
{
    /**
     * A list of traits that can be used for authorization.
     */
    private const AuthorizationAttributes = [
        Authorize::class,
        AllowAnonymous::class
    ];

    /**
     * TraitService constructor method.
     *
     * @param AuthManager $authManager
     */
    public function __construct(private readonly AuthManager $authManager)
    {
    }

    /**
     * @param class-string $className
     * @param string $methodName
     * @throws ReflectionException
     * @return Collection<ReflectionAttribute>
     */
    private function extractTraitsFromClassMethod(string $className, string $methodName): Collection
    {
        $reflectionClass = new ReflectionClass($className);
        $method = $reflectionClass->getMethod($methodName);
        return (new Collection($method->getAttributes()))
            ->filter(fn (ReflectionAttribute $attribute) => in_array($attribute->getName(), self::AuthorizationAttributes));
    }

    /**
     * @param class-string $className
     * @throws ReflectionException
     * @return Collection
     */
    private function extractTraitsFromClass(string $className): Collection
    {
        $reflectionClass = new ReflectionClass($className);
        return (new Collection($reflectionClass->getAttributes()))
            ->filter(fn (ReflectionAttribute $attribute) => in_array($attribute->getName(), self::AuthorizationAttributes));
    }

    /**
     * Compute the definitive list of traits that are being applied to a given method of a controller.
     *
     * @param class-string $className
     * @param string $methodName
     * @throws ReflectionException
     * @return Collection<ReflectionAttribute>
     */
    private function computeTraitsForClassMethod(string $className, string $methodName): Collection
    {
        $classTraits = $this->extractTraitsFromClass($className);
        $methodTraits = $this->extractTraitsFromClassMethod($className, $methodName);
        return $classTraits->merge($methodTraits);
    }

    /**
     * Compute the definitive list of traits that are being applied to a given closure.
     *
     * @param Closure $closure
     * @throws ReflectionException
     * @return Collection
     */
    private function computeTraitsForClosure(Closure $closure): Collection
    {
        $reflectionFunction = new ReflectionFunction($closure);
        return (new Collection($reflectionFunction->getAttributes()))
            ->filter(fn (ReflectionAttribute $attribute) => in_array($attribute->getName(), self::AuthorizationAttributes));
    }

    /**
     * Check whether an action can be accessed when guarded by the given traits.
     *
     * @param Collection<ReflectionAttribute>|ReflectionAttribute[] $traits
     * @return bool
     */
    private function canAccessThroughTraits(Collection|array $traits): bool
    {
        // Make sure the traits are a Collection
        if (is_array($traits)) {
            $traits = new Collection($traits);
        }

        // if AllowAnonymous is present, all other checks are bypassed
        if ($traits->some(fn (ReflectionAttribute $attribute) => $attribute->getName() === AllowAnonymous::class)) {
            return true;
        }

        // If Authorize is present, make sure the user is authenticated
        if ($traits->some(fn (ReflectionAttribute $attribute) => $attribute->getName() === Authorize::class)) {
            // Get the current user
            /** @var Model|HasPermissions $user */
            $user = $this->authManager->user();

            // If the user is not authenticated, no other checks can be performed
            if (!$user) {
                return abort(401);
            }

            // Loop through traits and make sure all of them pass
            $authorizationTraits = $traits->filter(fn (ReflectionAttribute $attribute) => $attribute->getName() === Authorize::class);

            /** @var ReflectionAttribute $trait */
            foreach ($authorizationTraits as $trait) {
                /** @var Collection $constraints */
                $constraints = (new Collection($trait->getArguments()))->flatten();

                // Check if none of the attributes are passing
                $fails = $constraints->isNotEmpty() && $constraints->doesntContain(function (mixed $constraint) use ($user) {
                    // Check for permission constraints
                    if ($constraint instanceof IPermissionEnum) {
                        return $user->hasPermission($constraint);
                    }

                    // Check for role constraints
                    if (is_string($constraint)) {
                        return $user->hasRole($constraint);
                    }

                    // If not recognized, don't take into account
                    return false;
                });

                // If checks failed, return false
                if ($fails) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check whether the given controller method can be accessed in the current request context.
     *
     * @param class-string $className
     * @param class-string $methodName
     * @throws ReflectionException
     * @return bool
     */
    public function canAccessControllerMethod(string $className, string $methodName): bool {
        $traits = $this->computeTraitsForClassMethod($className, $methodName);

        return $this->canAccessThroughTraits($traits);
    }

    /**
     * Check whether the given controller method can be accessed in the current request context.
     *
     * @param Closure $closure
     * @throws ReflectionException
     * @return bool
     */
    public function canAccessClosure(Closure $closure): bool
    {
        $traits = $this->computeTraitsForClosure($closure);
        return $this->canAccessThroughTraits($traits);
    }
}
