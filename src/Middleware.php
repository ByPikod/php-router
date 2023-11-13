<?php

namespace PHPRouter;

/**
 * MiddlewareChain interface
 * This is used in classes that allow adding middlewares.
 * @see MiddlewareChain
 * @see SubRouter,
 * @since 1.0.0
 */
interface Middleware
{
    /**
     * Use a middleware for a route or a group of routes.
     * @param callable $middleware The middleware to use.
     * @param string $path The path to add the middleware to.
     * @return MiddlewareChain The middleware adder itself to allow chaining.
     * @since 1.0.0
     */
    public function use(callable $middleware, string $path = ''): Middleware;
}
