<?php

namespace Codestage\Authorization\Services;

use Codestage\Authorization\Attributes\HandledBy;
use Codestage\Authorization\Contracts\Services\IPolicyService;
use Codestage\Authorization\Contracts\{IPolicy, IRequirement, IRequirementHandler};
use Illuminate\Contracts\Container\{BindingResolutionException, Container};
use Illuminate\Support\Collection;
use ReflectionClass;

/**
 * @internal
 */
class PolicyService implements IPolicyService
{
    /**
     * PolicyService constructor method.
     *
     * @param Container $_container
     */
    public function __construct(private readonly Container $_container)
    {
    }

    /**
     * Run the given policy.
     *
     * @param class-string $policy
     * @param array<string, mixed> $parameters
     * @throws BindingResolutionException
     * @return bool
     */
    public function runPolicy(string $policy, array $parameters = []): bool
    {
        // Resolve the policy instance from the Container
        /** @var IPolicy $policyInstance */
        $policyInstance = $this->_container->make($policy, $parameters);

        // Check that all the requirements defined by this policy pass
        $requirements = $policyInstance->requirements();

        foreach ($requirements as $requirement) {
            if (!$this->_checkRequirement($requirement)) {
                return false;
            }
        }

        // If no requirement fails, return true
        return true;
    }

    /**
     * Check whether the given requirement passes in the current context.
     *
     * @param IRequirement $requirement
     * @throws BindingResolutionException
     * @return bool
     */
    private function _checkRequirement(IRequirement $requirement): bool
    {
        // Get the handlers for this requirement
        $handlers = $this->getRequirementHandlers($requirement);

        // Return true if any of the handlers reports success
        foreach ($handlers as $handlerName) {
            /** @var IRequirementHandler $handler */
            $handler = $this->_container->make($handlerName);

            if ($handler->handle($requirement)) {
                return true;
            }
        }

        // If no handler returned successfully, this requirement fails
        return false;
    }

    /**
     * Get the handlers that can handle the given requirement.
     *
     * @param IRequirement $requirement
     * @return Collection<class-string<IRequirementHandler>>
     */
    private function getRequirementHandlers(IRequirement $requirement): Collection
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
