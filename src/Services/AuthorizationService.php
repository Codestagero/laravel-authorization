<?php

namespace Codestage\Authorization\Services;

use Codestage\Authorization\Attributes\HandledBy;
use Codestage\Authorization\Contracts\Services\IAuthorizationService;
use Codestage\Authorization\Contracts\{IPolicy, IRequirement, IRequirementHandler, IResourceRequirementHandler};
use Exception;
use Illuminate\Contracts\Container\{BindingResolutionException, Container};
use Illuminate\Support\{Collection, Enumerable};
use ReflectionClass;

class AuthorizationService implements IAuthorizationService
{
    /**
     * AuthorizationService constructor method.
     *
     * @param Container $_container
     */
    public function __construct(
        private readonly Container $_container
    ) {
    }

    /**
     * @inheritDoc
     */
    public function authorizePolicy(mixed $resource, IPolicy|string $policy): bool
    {
        // If the policy is not instantiated, get an instance
        if (\is_string($policy)) {
            $policy = $this->_container->make($policy);
        }

        // Check that all the requirements defined by this policy pass
        $requirements = $policy->requirements();

        return $this->authorizeRequirements($resource, $requirements);
    }

    /**
     * @inheritDoc
     */
    public function authorizeRequirements(mixed $resource, iterable $requirements): bool
    {
        foreach ($requirements as $requirement) {
            // Get all handlers that could handle this requirement
            $handlers = new Collection($this->_getRequirementHandlers($requirement));

            // If all of this requirement's handlers return false, then this requirement fails
            $fails = !$handlers->some(function (string $handlerClassName) use ($resource, $requirement) {
                $handler = $this->_container->make($handlerClassName);

                if ($handler instanceof IRequirementHandler) {
                    return $handler->handle($requirement);
                } else if ($handler instanceof IResourceRequirementHandler) {
                    if ($resource === null) {
                        throw new Exception('Attempt to use resource handler for authorizing without a resource.');
                    }

                    return $handler->handle($requirement, $resource);
                } else {
                    throw new BindingResolutionException($handlerClassName . ' was not resolved to a valid requirement handler.');
                }
            });

            // If this requirement failed, the whole check fails
            if ($fails) {
                return false;
            }
        }

        // If all requirements pass, return true
        return true;
    }

    /**
     * Get the handlers that can handle the given requirement.
     *
     * @param IRequirement $requirement
     * @return Enumerable<class-string<IRequirementHandler|IResourceRequirementHandler>>
     */
    private function _getRequirementHandlers(IRequirement $requirement): Enumerable
    {
        // Reflect on the requirement class
        $reflection = new ReflectionClass($requirement);

        // Accumulate the handlers that have been found
        $handlers = new Collection();

        // Get the attributes that describe authorization handlers
        $handlerAttributes = $reflection->getAttributes(HandledBy::class);

        // Extract handlers from each attribute
        foreach ($handlerAttributes as $attribute) {
            /** @var HandledBy $instance */
            $instance = $attribute->newInstance();

            if (\is_array($instance->handler)) {
                $handlers->push(...$instance->handler);
            } else if (\is_string($instance->handler)) {
                $handlers->push($instance->handler);
            }
        }

        // Return a list of unique handlers
        return $handlers->unique();
    }
}
