<?php

namespace PHPRouter;

use PHPRouter\Route;

/**
 * Router class
 * @since 1.0.0
 */
class Router implements RouteGroup
{
    protected array $executionTree = [];

    /**
     * Seperate the path into an array of strings.
     * @param string $path The path to seperate.
     * @return array The array of strings.
     * @example /test/:param/test -> [test, *, test]
     * @since 1.0.0
     */
    protected static function seperatePath(string $path): array
    {
        $path = trim($path, '/'); // Remove slashes from start and end
        $path = explode('/', $path); // Seperate to parts by '/'

        // Remove empty strings (eg. /test//test -> [test, test])
        $path = array_filter($path, function ($value) {
            return $value !== '';
        });

        // Wildcard support (eg. /test/:param/test -> [test, *, test])
        $path = array_map(function ($value) {
            if (substr($value, 0, 1) === ':')
                return '*';
            return $value;
        }, $path);

        return $path;
    }

    /**
     * Returns a branch from the execution tree.
     * @param string $path The path to get the branch from.
     * @param bool $popLast Whether to pop the last element of the path or not.
     * @return array The branch.
     * @example getBranch() -> $executionTree
     * @example getBranch('/test') -> $executionTree['test']
     * @example getBranch('/test/:param') -> $executionTree['test']['*']
     * @since 1.0.0
     */
    protected function &getBranch(string $path = ''): array
    {
        // Seperate to parts
        $path = self::seperatePath($path);

        // Get branch
        $branch = &$this->executionTree;
        foreach ($path as $value) {
            if (!array_key_exists($value, $branch)) {
                // Create new branch
                $branch[$value] = [];
            }
            $branch = &$branch[$value];
        }

        return $branch;
    }

    /**
     * Add middlewares
     * @param callable $middleware The middleware to add.
     * @return Middleware The middleware chain to allow adding middlewares.
     * @since 1.0.0
     */
    public function use(callable $middleware, $path = ''): Middleware
    {
        $this->getBranch($path)[] = $middleware;
        return $this;
    }

    /**
     * Add route
     * @param string $path The path of the route.
     * @param callable $callback The callback of the route.
     * @return MiddlewareChain The middleware adder to allow adding middlewares to the route.
     * @since 1.0.0
     */
    public function route(string $path, callable $callback): Middleware
    {
        $route = new Route($path, $callback);
        $this->getBranch($path)[] = $route;
        return new MiddlewareChain(function ($middleware) use ($route) {
            $route->middlewares[] = $middleware;
        });
    }

    /**
     * Add route group
     * @param string $path The path of the route group.
     * @return SubRouter The route group.
     * @since 1.0.0
     * @todo Implement this function
     */
    public function group(string $path): RouteGroup
    {
        $group = new SubRouter(
            // called when a route is added
            // @todo Implement this function
            function ($path, $callback) {
            },
            // called when a middleware is added
            // @todo Implement this function
            function ($path) {
            },
            // called when have to add a route group
            // @todo Implement this function
            function ($middleware) {
            }
        );
        $this->executionTree[] = $group;
        return $group;
    }

    /**
     * Walks through the specified branch and returns the middlewares and routes.
     * @param array $branch The branch to walk through.
     * @param Context $ctx The context to pass to the middlewares and routes.
     * @param bool $routes Whether to return the routes or not.
     * @return array The middlewares and routes.
     * @since 1.0.0
     */
    protected static function getExecutablesFromBranch(array $branch, $routes = false): array
    {
        $executables = [];
        foreach ($branch as $key => $value) {
            if (!is_numeric($key)) continue; // skip non-numeric keys (branches)

            // if the value is a route
            if ($value instanceof Route) {
                // skip if not executing routes
                if (!$routes)
                    continue;
                // execute middlewares
                foreach ($value->middlewares as $middleware) {
                    $executables[] = $middleware;
                }
                $executables[] = $value->callback;
            }

            // if the value is a middleware
            if (is_callable($value)) {
                $executables[] = $value;
            }
        }

        return $executables;
    }

    /**
     * Returns the executables of the specified path.
     * That list contains the top level middlewares and the routes.
     * @param string $path The path to get the executables from.
     * @return array The executables.
     * @since 1.0.0
     */
    protected function getExecutables(string $path): array
    {
        $pathArray = self::seperatePath($path);
        $branch = $this->executionTree;

        // Get executables ordered
        $executables = self::getExecutablesFromBranch($branch, (count($pathArray) === 0));
        for ($i = 0; $i < count($pathArray); $i++) {
            $value = $pathArray[$i];
            if (array_key_exists($value, $branch)) {
                // if there is a branch with the specified key
                $branch = $branch[$value];
            } elseif (array_key_exists('*', $branch)) {
                // if there is no branch with the specified key and there is a wildcard branch
                $branch = $branch['*'];
            } else {
                // if there is no branch with the specified key and there is no wildcard branch
                break;
            }
            $last = $i >= count($pathArray) - 1;
            $executables = $executables + self::getExecutablesFromBranch($branch, $last);
        }

        return $executables;
    }

    /**
     * Executes the execution tree.
     * @param string $path The path to execute.
     * @since 1.0.0
     */
    protected function executeTree(string $path): void
    {
        $executables = $this->getExecutables($path);

        // If no executables found, return 404
        if (count($executables) === 0) {
            echo "404 Not Found\n";
            http_response_code(404);
            return;
        }

        // Execute executables
        $i = 1;
        $ctx = new Context(function ($ctx) use (&$executables, &$i) {
            // this scope is called when the next() function is called in previous executable
            if ($i >= count($executables)) return; // if there are no more executables, return
            $fc = $executables[$i]; // get the next executable
            $i++; // increment the index
            $fc($ctx); // execute the next executable
        });
        $executables[0]($ctx); // execute the first executable
    }

    /**
     * Removes all routes and middlewares
     * @since 1.0.0
     */
    public function clear(): void
    {
        $this->executionTree = [];
    }

    /**
     * Run the router
     * @since 1.0.0
     */
    public function run(): void
    {
        $path = $_SERVER['REQUEST_URI'];
        $this->executeTree($path);
    }
}
