<?php

namespace PHPRouter;

use Closure;

/**
 * Context class
 * @since 1.0.0
 */
class Context
{
    private int $status = 200;
    public array $headers = [];
    private Closure $nextfc;

    /**
     * Create a new context
     * @param Closure $next Callback to be invoked when the next middleware is called
     * @since 1.0.0
     */
    public function __construct(callable $next)
    {
        $this->nextfc = $next;
    }

    /**
     * Set response status
     * @param int $status Status code
     * @since 1.0.0
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * Set response header
     * @param string $key Header key
     * @param string $value Header value
     * @since 1.0.0
     */
    public function setHeader(string $key, string $value): void
    {
        $this->headers[$key] = $value;
    }

    /**
     * Invoke the next middleware (or end the request if there are no more middlewares)
     * @since 1.0.0
     */
    public function next(): void
    {
        ($this->nextfc)($this);
    }

    /**
     * Apply response headers and status
     * @since 1.0.0
     */
    public function done(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        die();
    }
}
