<?php

namespace Codestage\Authorization\Services;

use Codestage\Authorization\Contracts\IPolicy;
use Codestage\Authorization\Contracts\IPolicyProvider;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Exceptions\BackedEnumCaseNotFoundException;
use Illuminate\Routing\ImplicitRouteBinding;
use Illuminate\Routing\Router;
use ReflectionClass;
use ReflectionException;

/**
 * @template TProvides of IPolicy
 */
class DefaultPolicyProvider implements IPolicyProvider
{
    /**
     * DefaultPolicyProvider constructor method.
     *
     * @param Container $_container
     * @param Router $_router
     */
    public function __construct(
        private readonly Container $_container,
        private readonly Router $_router
    ) {
    }

    /**
     * Create an instance of the requested policy, binding the given parameters.
     *
     * @param class-string<TProvides> $policy
     * @param array $parameters
     * @throws BindingResolutionException
     * @throws AuthorizationException
     * @return TProvides
     */
    public function make(string $policy, array $parameters): IPolicy
    {
        // Get the parameters that need to be substituted in routing
        $routeParameters = $this->_neededRouteParameters($parameters);

        if (!empty($routeParameters)) {
            // Remove route parameters from the parameters array
            array_filter($parameters, function (mixed $value, mixed $key) {
                return !is_numeric($key) || !is_string($value);
            }, ARRAY_FILTER_USE_BOTH);

            // Make sure the current route is accessible
            $route = $this->_router->getCurrentRoute();

            if (!$route) {
                throw new AuthorizationException('Routing context could not be determined when providing policy ' . $policy);
            }

            // Make sure that all requested parameters can actually be found in the route before trying to resolve them
            foreach ($routeParameters as $parameter) {
                if (!$route->hasParameter($parameter)) {
                    throw new BindingResolutionException('Requested route parameter ' . $parameter . ' for policy ' . $policy . ', but it does not exist.');
                }
            }

            // Resolve route implicit bindings
            try {
                ImplicitRouteBinding::resolveForRoute($this->_container, $route);
            } catch (ModelNotFoundException) {
                throw new AuthorizationException('Requested route parameters for policy ' . $policy . ' could not be resolved.');
            } catch (BackedEnumCaseNotFoundException) {
                throw new BindingResolutionException('Requested route parameters for policy ' . $policy . ' could not be resolved.');
            }

            // Add the substituted bindings to the parameters
            foreach ($routeParameters as $parameter) {
                $parameters[$parameter] = $route->parameter($parameter);
            }
        }

        // Let the container provide the rest of the parameters
        return $this->_container->make($policy, $parameters);
    }

    /**
     * Get the type hint given in the policy constructor for the requested parameter.
     *
     * @param class-string $policy
     * @param string $parameter
     * @throws ReflectionException
     * @return class-string
     */
    public function _getParameterTypeHint(string $policy, string $parameter): string
    {
        // Reflect on the policy class
        $reflection = new ReflectionClass($policy);

        // Make sure the constructor data is accessible
        $constructor = $reflection->getConstructor();
        if (!$constructor) {
            throw new ReflectionException('Could not find constructor for policy ' . $policy);
        }

        // Find the requested parameter and return its type
        $constructorParameters = $constructor->getParameters();
        foreach ($constructorParameters as $constructorParameter) {
            if (!strcmp($constructorParameter->getName(), $parameter)) {
                return $constructorParameter->getType()->getName();
            }
        }

        // Throw exception if parameter could not be found
        throw new ReflectionException('Could not find parameter ' . $parameter . ' in constructor for policy ' . $policy);
    }

    /**
     * Get the parameters that need to be filled in from the router.
     *
     * @param array $parameters
     * @return array
     */
    private function _neededRouteParameters(array $parameters): array
    {
        $needed = [];
        foreach ($parameters as $key => $value) {
            if (is_numeric($key) && is_string($value)) {
                $needed[] = $value;
            }
        }
        return $needed;
    }
}
