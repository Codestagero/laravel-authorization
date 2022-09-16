<?php

namespace Codestage\Authorization\Services;

use Closure;
use Codestage\Authorization\Attributes\{AllowAnonymous, Authorize};
use Codestage\Authorization\Authorization\Requirements\HasPermissionRequirement;
use Codestage\Authorization\Authorization\Requirements\HasRoleRequirement;
use Codestage\Authorization\Contracts\{IPermissionEnum,
    IPolicy,
    IRequirement,
    Services\IAuthorizationService,
    Services\IPolicyService};
use Codestage\Authorization\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Guard as AuthManager;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use function in_array;
use function is_array;

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
     * @param AuthManager $authManager
     * @param IPolicyService $policyService
     */
    public function __construct(private readonly AuthManager $authManager, private readonly IPolicyService $policyService)
    {
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
            ->filter(fn (ReflectionAttribute $attribute) => in_array($attribute->getName(), self::AuthorizationAttributes));
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
            ->filter(fn (ReflectionAttribute $attribute) => in_array($attribute->getName(), self::AuthorizationAttributes));
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

        // if AllowAnonymous is present, all other checks are bypassed
        if ($attributes->some(fn (ReflectionAttribute $attribute) => $attribute->getName() === AllowAnonymous::class)) {
            return true;
        }

        // If Authorize is present, make sure the user is authenticated
        if ($attributes->some(fn (ReflectionAttribute $attribute) => $attribute->getName() === Authorize::class)) {
            // Get the current user
            /** @var Model|HasPermissions $user */
            $user = $this->authManager->user();

            // If the user is not authenticated, no other checks can be performed
            if (!$user) {
                return abort(401);
            }

            // Loop through attributes and make sure all of them pass
            $authorizationAttributes = $attributes->filter(fn (ReflectionAttribute $attribute) => $attribute->getName() === Authorize::class);

            /** @var ReflectionAttribute<Authorize> $attributeReflection */
            foreach ($authorizationAttributes as $attributeReflection) {
                $attribute = $attributeReflection->newInstance();

                if (!$this->checkAttributePasses($attribute)) {
                    return false;
                }
            }
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
        $computedPolicies = new Collection($attribute->policies ?? []);

        // If this attribute has permission requirements, add them to the computed policies list
        if ($attribute->permissions) {
            if (!is_array($attribute->permissions)) {
                $attribute->permissions = [$attribute->permissions];
            }

            // Add a new policy that requires the permissions
            $computedPolicies->prepend(new class ($attribute->permissions) implements IPolicy {
                /**
                 * @param IPermissionEnum[] $permissions
                 */
                public function __construct(private readonly array $permissions)
                {
                }

                /**
                 * The list of requirements that need to be fulfilled in order to complete this policy.
                 *
                 * @return array<int, IRequirement>
                 */
                public function requirements(): array
                {
                    return [new HasPermissionRequirement($this->permissions)];
                }
            });
        }

        // If this attribute has role requirements, add them to the computed policies list
        if ($attribute->roles) {
            if (!is_array($attribute->roles)) {
                $attribute->roles = [$attribute->roles];
            }

            // Add a new policy that requires the roles
            $computedPolicies->prepend(new class ($attribute->roles) implements IPolicy {
                /**
                 * @param string[] $roles
                 */
                public function __construct(private readonly array $roles)
                {
                }

                /**
                 * The list of requirements that need to be fulfilled in order to complete this policy.
                 *
                 * @return array<int, IRequirement>
                 */
                public function requirements(): array
                {
                    return [new HasRoleRequirement($this->roles)];
                }
            });
        }

        // Run the computed policies
        foreach ($computedPolicies as $policy) {
            if ($this->policyService->runPolicy($policy)) {
                return true;
            }
        }

        // If no requirement passes, return true only if this attribute doesn't define any requirements
        return $computedPolicies->isEmpty();
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
