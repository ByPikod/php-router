<?php

namespace PHPRouter;

use Closure;

/**
 * This is a mirror of Router with the functions redirected to the parent router.
 * @since 1.0.0
 */
class SubRouter implements RouteGroup
{
    private Closure $cb_route;
    private Closure $cb_group;
    private Closure $cb_use;

    /**
     * Constructor
     * @since 1.0.0
     */
    public function __construct(
        Closure $cb_route,
        Closure $cb_group,
        Closure $cb_use
    ) {
        $this->cb_route = $cb_route;
        $this->cb_group = $cb_group;
        $this->cb_use = $cb_use;
    }

    /**
     * This function redirected to the parent router.
     * @param Closure $middleware The middleware to add.
     * @return Middleware The middleware chain to allow adding middlewares.
     * @since 1.0.0
     */
    public function use(Closure $middleware, $path = ''): Middleware
    {
        ($this->cb_use)($middleware, $path);
        return $this;
    }

    /**
     * This function redirected to the parent router.
     * @param string $path The path of the route.
     * @param callable $callback The callback of the route.
     * @return MiddlewareChain The middleware adder to allow adding middlewares to the route.
     * @since 1.0.0
     */
    public function route(string $path, Closure $callback): Middleware
    {
        ($this->cb_route)($path, $callback);
        return $this;
    }

    /**
     * This function redirected to the parent router.
     * @param string $path The path of the route group to add.
     * @return SubRouter The route group.
     * @since 1.0.0
     */
    public function group(string $path): RouteGroup
    {
        ($this->cb_group)($path);
        return $this;
    }
}
