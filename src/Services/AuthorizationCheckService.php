<?php

namespace Codestage\Authorization\Services;

use Closure;
use Codestage\Authorization\Attributes\{AllowAnonymous, Authorize};
use Codestage\Authorization\Contracts\{IPermissionEnum,
    IPolicy,
    Services\IAuthorizationCheckService,
    Services\IAuthorizationService};
use Codestage\Authorization\Requirements\{HasPermissionRequirement, HasRoleRequirement};
use Codestage\Authorization\Traits\HasPermissions;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Guard as AuthManager;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\{Collection, Enumerable};
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use function get_class;
use function in_array;
use function is_string;

/**
 * @internal
 */
class AuthorizationCheckService implements IAuthorizationCheckService
{
    /**
     * A list of attributes that can be used for authorization.
     */
    private const AuthorizationAttributes = [
        Authorize::class,
        AllowAnonymous::class
    ];

    /**
     * AuthorizationCheckService constructor method.
     *
     * @param AuthManager $_authManager
     * @param Container $_container
     * @param IAuthorizationService $_authorizationService
     */
    public function __construct(
        private readonly AuthManager $_authManager,
        private readonly Container $_container,
        private readonly IAuthorizationService $_authorizationService
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
     * @return Enumerable<ReflectionAttribute>
     */
    private function extractAttributesFromClassMethod(string $className, string $methodName): Enumerable
    {
        $reflectionClass = new ReflectionClass($className);
        $method = $reflectionClass->getMethod($methodName);

        return (new Collection($method->getAttributes()))
            ->filter(fn (ReflectionAttribute $attribute) => $this->isAuthorizationAttribute($attribute->getName()));
    }

    /**
     * @param class-string $className
     * @throws ReflectionException
     * @return Enumerable<ReflectionAttribute>
     */
    private function extractAttributesFromClass(string $className): Enumerable
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
     * @return Enumerable<ReflectionAttribute>
     */
    private function computeAttributesForClassMethod(string $className, string $methodName): Enumerable
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
     * @return Enumerable<ReflectionAttribute>
     */
    private function computeAttributesForClosure(Closure $closure): Enumerable
    {
        $reflectionFunction = new ReflectionFunction($closure);

        return (new Collection($reflectionFunction->getAttributes()))
            ->filter(fn (ReflectionAttribute $attribute) => in_array($attribute->getName(), self::AuthorizationAttributes));
    }

    /**
     * Check whether an action can be accessed when guarded by the given attributes.
     *
     * @param Enumerable<ReflectionAttribute>|Arrayable<ReflectionAttribute>|iterable<ReflectionAttribute> $attributes
     * @throws AuthenticationException
     * @return bool
     */
    private function canAccessThroughAttributes(Enumerable|iterable|Arrayable $attributes): bool
    {
        // Make sure the attributes are enumerable
        if (!($attributes instanceof Enumerable)) {
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
     * @return bool
     */
    private function checkAttributePasses(Authorize $attribute): bool
    {
        // If there are policies configured, check if any of them passes.
        $policies = new Collection($attribute->policies);

        // Add role policies
        if (!!$attribute->roles) {
            $roles = new Collection($attribute->roles);

            foreach ($roles as $role) {
                $policies->push(new class($role) implements IPolicy {
                    /** Constructor method. */
                    public function __construct(public readonly string $role)
                    {
                    }

                    /** @inheritDoc */
                    public function requirements(): array
                    {
                        return [new HasRoleRequirement($this->role)];
                    }
                });
            }
        }

        // Add permission policies
        if (!!$attribute->permissions) {
            $permissions = new Collection($attribute->permissions);

            foreach ($permissions as $permission) {
                $policies->push(new class($permission) implements IPolicy {
                    /** Constructor method. */
                    public function __construct(public readonly IPermissionEnum $permission)
                    {
                    }

                    /** @inheritDoc */
                    public function requirements(): array
                    {
                        return [new HasPermissionRequirement($this->permission)];
                    }
                });
            }
        }

        // If no policies are present, the attribute cannot fail
        if ($policies->isEmpty()) {
            return true;
        }

        // Instantiate all policies and run them
        return $policies->map(fn (string|IPolicy $policy) => is_string($policy) ? $this->_container->make($policy) : $policy)
            ->some(fn (IPolicy $policy) => $this->_authorizationService->authorizePolicy(null, $policy));
    }

    /**
     * Check whether the given controller method can be accessed in the current request context.
     *
     * @param class-string $className
     * @param class-string $methodName
     * @throws AuthenticationException
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
     * @throws AuthenticationException
     * @throws ReflectionException
     * @return bool
     */
    public function canAccessClosure(Closure $closure): bool
    {
        $attributes = $this->computeAttributesForClosure($closure);

        return $this->canAccessThroughAttributes($attributes);
    }
}
