<?php

namespace Codestage\Authorization\Middleware;

use Closure;
use Codestage\Authorization\Contracts\Services\IAuthorizationCheckService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use ReflectionException;
use Throwable;

readonly class AuthorizationMiddleware
{
    /**
     * AuthorizeMiddleware constructor method.
     */
    public function __construct(
        private IAuthorizationCheckService $_authorizationService
    ) {
    }

    /**
     * Check if the requested action is authorized to be performed in the current request context.
     *
     * @param Request $request
     * @param Closure $next
     * @throws AuthorizationException
     * @throws ReflectionException
     * @throws BindingResolutionException
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->route()) {
            // If the request is made to an action inside a controller
            if ($this->_requestUsesController($request)) {
                $controller = $request->route()->getControllerClass();
                $method = $request->route()->getActionMethod();

                // If the controller and method are the same, then this is a Single Action controller
                if (!strcmp($controller, $method)) {
                    $method = '__invoke';
                }

                if (!$this->_authorizationService->canAccessControllerMethod($controller, $method)) {
                    throw new AuthorizationException();
                }
            }

            // If the request is made to an action inside a Closure
            if ($request->route()->getAction('uses') instanceof Closure) {
                if (!$this->_authorizationService->canAccessClosure($request->route()->getAction('uses'))) {
                    throw new AuthorizationException();
                }
            }
        }

        // If checks pass, the request can continue through the pipeline
        return $next($request);
    }

    /**
     * Check if the given request uses a controller as its action.
     *
     * @param Request $request
     * @return bool
     */
    private function _requestUsesController(Request $request): bool
    {
        try {
            return !!$request->route()->getControllerClass();
        } catch (Throwable) {
            return false;
        }
    }
}
