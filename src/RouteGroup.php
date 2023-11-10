<?php

namespace PHPRouter;

use Closure;

/**
 * RouteGroup interface
 * This is used to allow the router to add route groups and routes.
 * @see Router
 * @see SubRouter
 * @since 1.0.0
 */
interface RouteGroup extends Middleware
{
    /**
     * Add route
     * @param string $path The path of the route.
     * @param callable $callback The callback of the route.
     * @since 1.0.0
     */
    public function route(string $path, callable $callback): Middleware;

    /**
     * Add route group
     * @param string $path The path of the route group.
     * @since 1.0.0
     */
    public function group(string $path): RouteGroup;
}
