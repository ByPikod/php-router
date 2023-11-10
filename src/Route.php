<?php

namespace PHPRouter;

use Closure;

/**
 * Route class
 * @since 1.0.0
 */
class Route
{
    public string $path;
    public Closure $callback;
    public array $middlewares = [];

    /**
     * Route constructor.
     * @param string $path The path of the route.
     * @param Closure $callback The callback to be called when the route is matched.
     * @since 1.0.0
     */
    public function __construct(string $path, callable $callback)
    {
        $this->path = $path;
        $this->callback = $callback;
    }

    public function __toString(): string
    {
        return $this->path;
    }
}
