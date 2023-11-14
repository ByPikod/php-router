<?php

namespace PHPRouter;

/**
 * Router class
 * @since 1.0.0
 */
class Router extends SubRouter
{
    public function __construct()
    {
        parent::__construct();
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
