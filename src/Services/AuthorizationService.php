<?php

namespace Codestage\Authorization\Services;

use Closure;
use Codestage\Authorization\Attributes\{AllowAnonymous, Authorize};
use Codestage\Authorization\Contracts\{Services\IAuthorizationService, Services\IPolicyService};
use Codestage\Authorization\Traits\HasPermissions;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Guard as AuthManager;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use function count;
use function get_class;
use function in_array;
use function is_array;
use function is_string;

/**
 * @internal
 */
class AuthorizationService implements IAuthorizationService
{
    /**
     * A list of attributes that can be used for authorization.
     */
    private const AuthorizationAttributes = [
        Authorize::class,
        AllowAnonymous::class
    ];

    /**
     * AuthorizationService constructor method.
     *
     * @param AuthManager $_authManager
     * @param IPolicyService $_authManager
     */
    public function __construct(
        private readonly AuthManager $_authManager,
        private readonly IPolicyService $_policyService
    ) {
    }

    /**
     * Check if the given input is an Authorization attribute.
     *
     * @param object|string $class
     * @return bool
     */
    private function isAuthorizationAttribute(object|string $class): bool
    {
        $className = is_string($class) ? $class : get_class($class);

        foreach (self::AuthorizationAttributes as $attribute) {
            if (is_subclass_of($className, $attribute) || $className === $attribute) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param class-string $className
     * @param string $methodName
     * @throws ReflectionException
     * @return Collection<ReflectionAttribute>
     */
    private function extractAttributesFromClassMethod(string $className, string $methodName): Collection
    {
        $reflectionClass = new ReflectionClass($className);
        $method = $reflectionClass->getMethod($methodName);

        return (new Collection($method->getAttributes()))
            ->filter(fn (ReflectionAttribute $attribute) => $this->isAuthorizationAttribute($attribute->getName()));
    }

    /**
     * @param class-string $className
     * @throws ReflectionException
     * @return Collection
     */
    private function extractAttributesFromClass(string $className): Collection
    {
        $reflectionClass = new ReflectionClass($className);

        return (new Collection($reflectionClass->getAttributes()))
            ->filter(fn (ReflectionAttribute $attribute) => $this->isAuthorizationAttribute($attribute->getName()));
    }

    /**
     * Compute the definitive list of attributes that are being applied to a given method of a controller.
     *
     * @param class-string $className
     * @param string $methodName
     * @throws ReflectionException
     * @return Collection<ReflectionAttribute>
     */
    private function computeAttributesForClassMethod(string $className, string $methodName): Collection
    {
        $classAttributes = $this->extractAttributesFromClass($className);
        $methodAttributes = $this->extractAttributesFromClassMethod($className, $methodName);

        return $classAttributes->merge($methodAttributes);
    }

    /**
     * Compute the definitive list of attributes that are being applied to a given closure.
     *
     * @param Closure $closure
     * @throws ReflectionException
     * @return Collection
     */
    private function computeAttributesForClosure(Closure $closure): Collection
    {
        $reflectionFunction = new ReflectionFunction($closure);

        return (new Collection($reflectionFunction->getAttributes()))
            ->filter(fn (ReflectionAttribute $attribute) => in_array($attribute->getName(), self::AuthorizationAttributes));
    }

    /**
     * Check whether an action can be accessed when guarded by the given attributes.
     *
     * @param Collection<ReflectionAttribute>|ReflectionAttribute[] $attributes
     * @throws BindingResolutionException
     * @return bool
     */
    private function canAccessThroughAttributes(Collection|array $attributes): bool
    {
        // Make sure the attributes are a Collection
        if (is_array($attributes)) {
            $attributes = new Collection($attributes);
        }

        // Instantiate attributes
        $attributes = $attributes->map(fn (ReflectionAttribute $attribute) => $attribute->newInstance());

        // if AllowAnonymous is present, all other checks are bypassed
        if ($attributes->some(fn (object $attribute) => $attribute instanceof AllowAnonymous)) {
            return true;
        }

        // Only take into account authorization attributes
        $authorizationAttributes = $attributes->filter(fn (object $attribute) => $attribute instanceof Authorize);

        // If Authorize is present, make sure the user is authenticated
        if ($authorizationAttributes->isNotEmpty()) {
            // Get the current user
            /** @var Model|HasPermissions $user */
            $user = $this->_authManager->user();

            // If the user is not authenticated, no other checks can be performed
            if (!$user) {
                throw new AuthenticationException();
            }

            // Loop through authorization attributes and make sure that every one of them passes
            return $authorizationAttributes->every(fn (Authorize $attribute) => $this->checkAttributePasses($attribute));
        }

        return true;
    }

    /**
     * Check if requirements defined by the given attribute pass in the current context.
     *
     * @param Authorize $attribute
     * @throws BindingResolutionException
     * @return bool
     */
    private function checkAttributePasses(Authorize $attribute): bool
    {
        // If no policies are provided, no checks can fail
        if (!$attribute->policies || (is_array($attribute->policies) && count($attribute->policies) === 0)) {
            return true;
        }

        // If there are policies configured, check if any of them passes.
        $policies = new Collection($attribute->policies);

        return $policies->isEmpty() || $policies->some(fn (string $policyName) => $this->_policyService->runPolicy($policyName, $attribute->parameters));
    }

    /**
     * Check whether the given controller method can be accessed in the current request context.
     *
     * @param class-string $className
     * @param class-string $methodName
     * @throws BindingResolutionException
     * @throws ReflectionException
     * @return bool
     */
    public function canAccessControllerMethod(string $className, string $methodName): bool
    {
        $attributes = $this->computeAttributesForClassMethod($className, $methodName);

        return $this->canAccessThroughAttributes($attributes);
    }

    /**
     * Check whether the given controller method can be accessed in the current request context.
     *
     * @param Closure $closure
     * @throws BindingResolutionException
     * @throws ReflectionException
     * @return bool
     */
    public function canAccessClosure(Closure $closure): bool
    {
        $attributes = $this->computeAttributesForClosure($closure);

        return $this->canAccessThroughAttributes($attributes);
    }
}
