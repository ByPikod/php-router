<?php

namespace PHPRouter;

use PHPRouter\Utilities;

/**
 * RouteGroup interface
 * This is used to allow the router to add route groups and routes.
 * @see Router
 * @see SubRouter
 * @since 1.0.0
 */
class SubRouter implements Middleware
{
    protected string $directoryName = ""; // The name of the current directory
    protected SubRouter | null $parent = null; // The parent SubRouter
    protected array $fullPath; // The full path of the SubRouter
    protected array $executionTree = [];

    /**
     * Constructor
     * @param string $directoryName The name of the current directory.
     * @param SubRouter $parent The parent SubRouter.
     * @since 1.0.1
     */
    protected function __construct(string $directoryName = "", SubRouter $parent = null)
    {
        $this->directoryName = $directoryName;
        $this->parent = $parent;
        $this->fullPath = $this->findFullPath();
    }

    /**
     * Returns the full path which cached with findFullPath().
     * @return array The full path.
     * @see findFullPath()
     * @since 1.0.1
     */
    public function getFullPath(): array
    {
        return $this->fullPath;
    }

    /**
     * Returns the upper Router.
     * @return SubRouter The upper Router.
     * @since 1.0.1
     */
    public function getParent(): SubRouter
    {
        return $this->parent;
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
        $subRouter = $this->getSubRouter($path);
        $route = new Route($subRouter, $callback);
        $subRouter->executionTree[] = $route;
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
    public function group(string $path): SubRouter
    {
        return $this->getSubRouter($path);
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
     * Returns the full path of the SubRouter.
     * This is used to find the full path of the SubRouter.
     * You can use getFullPath() to get cached full path.
     * @return array The full path.
     * @see getFullPath()
     * @since 1.0.1
     */
    protected function findFullPath(): array
    {
        $path = [];
        $parent = $this;
        while (!is_null($parent)) {
            $dirName = $parent->directoryName;
            if ($dirName === '') {
                $parent = $parent->parent;
                continue;
            }
            $path[] = $dirName;
            $parent = $parent->parent;
        }
        $path = array_reverse($path);
        return $path;
    }

    /**
     * Matches the subrouter with its name.
     * This only looks for current directory. Meanwhile getSubRouter() looks for all directories.
     * @param string $dirName The name of the subrouter.
     * @return SubRouter Matching SubRouter
     * @return null If no matching SubRouter found.
     * @since 1.0.1
     * @see getSubRouter()
     */
    public function findSubRouter($dirName): SubRouter | null
    {
        foreach ($this->executionTree as $value) {
            if ($value instanceof SubRouter && $value->directoryName === $dirName) return $value;
        }
        return null;
    }

    /**
     * Returns the requested SubRouter from the execution tree.
     * @param string $path The path to get the SubRouter from.
     * @param bool $create Whether to create the SubRouter if it doesn't exist or not.
     * @return SubRouter The SubRouter.
     * @see getBranch()
     * @since 1.0.1
     */
    protected function getSubRouter(string $path = '', $create = true): SubRouter | null
    {
        // Seperate to parts
        $path = Utilities::seperatePath($path);

        // Get branch
        $branch = &$this;
        foreach ($path as $value) {
            $subRouter = $branch->findSubRouter($value);
            if (is_null($subRouter)) {
                if (!$create) return null;
                // Create new branch
                $subRouter = new SubRouter($value, $branch);
                $branch->executionTree[] = $subRouter;
            }
            $branch = $subRouter;
        }

        return $branch;
    }

    /**
     * Returns a execution tree of a sub router.
     * @param string $path The path to get the branch from.
     * @param bool $create Whether to create the branch if it doesn't exist or not.
     * @return array The branch.
     * @return null If no branch found.
     * @example getBranch() -> $executionTree
     * @example getBranch('/test') -> $executionTree['test']
     * @since 1.0.0
     */
    protected function &getBranch(string $path = '', $create = true): array
    {
        $sb = $this->getSubRouter($path, $create);
        if ($sb === null) throw new \Exception("Branch not found and couldn't be created.");
        return $sb->executionTree;
    }

    /**
     * Returns the executables of the specified path.
     * That list contains the top level middlewares and the routes.
     * @param array $path The path to get the executables from.
     * @param array $params The parameters extracted from the URL. This is used to pass parameters to the route.
     * @return array The executables.
     * @since 1.0.0
     */
    protected function getExecutables(array $path = [], $params = []): array
    {
        $branch = $this->executionTree;
        $executables = [];
        // this loop will get us every middlewares and routes in the path ordered
        foreach ($branch as $key => $value) {
            if ($value instanceof Route) {
                // if the value is a route
                if (sizeof($path) !== 0)
                    // skip if not last directory.
                    // only routes in the last directory will be executed
                    continue;
                // add route middlewares to the list as well
                foreach ($value->middlewares as $middleware)
                    $executables[] = $middleware;
                // route executed after middlewares
                $executables[] = $value->callback;
            } elseif (is_callable($value)) {
                // middlewares on the path will be executed anyway
                $executables[] = $value;
            } elseif ($value instanceof SubRouter) {
                // if the value is a subrouter
                $dirName = $value->directoryName;
                $isWildcard = str_starts_with($dirName, ':');
                if (
                    sizeof($path) < 1 || // if there are no more directories in the path
                    !$isWildcard && // or the next directory is not a wildcard
                    $dirName !== $path[0] // and the next directory is not the subrouter's name
                )
                    // make sure SubRouter is not the next directory so we don't execute it
                    continue;
                array_pop($path); // remove the first directory since it's going to be executed
                $executables = array_merge($executables, $value->getExecutables($path));
            }
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
        $path = Utilities::seperatePath($path);
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
}
