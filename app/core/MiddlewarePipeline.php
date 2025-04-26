<?php

namespace App\Core;

class MiddlewarePipeline {
    /** @var callable[] */
    private $middlewares = [];

    /**
     * Add a middleware to the pipeline.
     * @param callable $middleware Should return false to halt execution.
     */
    public function add(callable $middleware): void {
        $this->middlewares[] = $middleware;
    }

    /**
     * Execute all middlewares in sequence.
     * @return bool False if any middleware returns false, true otherwise.
     */
    public function run(): bool {
        foreach ($this->middlewares as $middleware) {
            $result = call_user_func($middleware);
            if ($result === false) {
                return false;
            }
        }
        return true;
    }
}
