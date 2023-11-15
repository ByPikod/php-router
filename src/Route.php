<?php

namespace PHPRouter;

use Closure;

/**
 * Route class
 * @since 1.0.0
 */
class Route
{
    public SubRouter $parent;
    public Closure $callback;
    public array $middlewares = [];

    /**
     * Route constructor.
     * @param SubRouter $parent The parent sub router.
     * @param Closure $callback The callback to be called when the route is matched.
     * @since 1.0.0
     */
    public function __construct(SubRouter $parent, callable $callback)
    {
        $this->parent = $parent;
        $this->callback = $callback;
    }

    public function __toString(): string
    {
        return implode("/", $this->parent->getFullPath());
    }

    /**
     * Extracts the parameters from the URL.
     * @param string $path The URL to extract the parameters from.
     * @return array The parameters extracted from the URL.
     * @since 1.0.1
     */
    public function extractParamsFromURL(string $url): array
    {
        $routeURL = $this->parent->getFullPath();
        $url = Utilities::seperatePath($url);
        $params = [];
        foreach ($path as $index => $pathPart) {
            if (preg_match("/^:/", $pathPart)) {
                $params[substr($pathPart, 1)] = $url[$index];
            }
        }
        return $params;
    }
}
