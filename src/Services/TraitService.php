<?php

namespace Codestage\Authorization\Services;

use Closure;
use Codestage\Authorization\Attributes\{AllowAnonymous, Authorize};
use Codestage\Authorization\Authorization\Requirements\HasPermissionRequirement;
use Codestage\Authorization\Authorization\Requirements\HasRoleRequirement;
use Codestage\Authorization\Contracts\{IPermissionEnum,
    IPolicy,
    IRequirement,
    Services\IPolicyService,
    Services\ITraitService};
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
     * @throws BindingResolutionException
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

            /** @var ReflectionAttribute<Authorize> $traitReflection */
            foreach ($authorizationTraits as $traitReflection) {
                $trait = $traitReflection->newInstance();

                if (!$this->checkTraitFails($trait)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if requirements defined by the given trait pass in the current context.
     *
     * @param Authorize $trait
     * @throws BindingResolutionException
     * @return bool
     */
    private function checkTraitFails(Authorize $trait): bool
    {
        $computedPolicies = new Collection($trait->policies ?? []);

        // If this trait has permission requirements, add them to the computed policies list
        if ($trait->permissions) {
            if (!is_array($trait->permissions)) {
                $trait->permissions = [$trait->permissions];
            }

            // Add a new policy that requires the permissions
            $computedPolicies->prepend(new class ($trait->permissions) implements IPolicy {
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

        // If this trait has role requirements, add them to the computed policies list
        if ($trait->roles) {
            if (!is_array($trait->roles)) {
                $trait->roles = [$trait->roles];
            }

            // Add a new policy that requires the roles
            $computedPolicies->prepend(new class ($trait->roles) implements IPolicy {
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

        // If no requirement passes, return true only if this trait doesn't define any requirements
        return !$trait->policies && !$trait->roles && !$trait->permissions;
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
        $traits = $this->computeTraitsForClassMethod($className, $methodName);

        return $this->canAccessThroughTraits($traits);
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
        $traits = $this->computeTraitsForClosure($closure);

        return $this->canAccessThroughTraits($traits);
    }
}
