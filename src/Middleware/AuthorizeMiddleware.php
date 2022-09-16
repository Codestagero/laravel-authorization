<?php

namespace Codestage\Authorization\Middleware;

use Closure;
use Codestage\Authorization\Contracts\Services\IAuthorizationService;
use Illuminate\Http\Request;
use ReflectionException;
use Throwable;

class AuthorizeMiddleware
{
    /**
     * AuthorizeMiddleware constructor method.
     */
    public function __construct(private readonly IAuthorizationService $traitService)
    {
    }

    /**
     * Check if the requested action is authorized to be performed in the current request context.
     *
     * @param Request $request
     * @param Closure $next
     * @throws ReflectionException
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // If the request is made to an action inside a controller
        if ($this->_requestUsesController($request)) {
            $controller = $request->route()->getControllerClass();
            $method = $request->route()->getActionMethod();

            // If the controller and method are the same, then this is a Single Action controller
            if (!strcmp($controller, $method)) {
                $method = '__invoke';
            }

            if (!$this->traitService->canAccessControllerMethod($controller, $method)) {
                abort(403);
            }
        }

        // If the request is made to an action inside a Closure
        if ($request->route()->getAction('uses') instanceof Closure) {
            if (!$this->traitService->canAccessClosure($request->route()->getAction('uses'))) {
                abort(403);
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