<?php

/**
 * Class Router
 *
 * A simple Router class to manage URL-to-callback mapping and dispatch requests to the appropriate controllers and methods.
 * The class supports defining routes, matching URLs against patterns, and invoking callbacks for matched routes.
 */
class Router {
    /**
     * @var array $routes Associative array of route patterns and their corresponding callbacks.
     */
    private $routes = [];

    /**
     * Adds a new route to the router.
     *
     * @param string $pattern  The URL pattern to match (regular expression).
     * @param string $callback The callback for the route in the format "Controller@Method".
     *
     * @return void
     */
    public function add($pattern, $callback) {
        $this->routes[$pattern] = $callback;
    }

    /**
     * Dispatches a request to the appropriate route callback.
     *
     * @param string $url The URL to match against the defined routes.
     *
     * @return void Outputs the result of the invoked callback or a 404 error if no route matches.
     */
    public function dispatch($url) {
        // remove query string variables from url
        $url = strtok($url, '?');

        foreach ($this->routes as $pattern => $callback) {
            // check if the URL matches the current route pattern
            if (preg_match('#^' . $pattern . '$#', $url, $matches)) {
                // remove the exact match to extrat parameters
                array_shift($matches);
                return $this->invoke($callback, $matches);
            }
        }

        // if there was no match at all, return 404
        http_response_code(404);
        echo '404 page not found';
    }

    /**
     * Invokes the callback for a matched route.
     *
     * @param string $callback The callback for the route in the format "Controller@Method".
     * @param array  $params   Parameters extracted from the route pattern.
     *
     * @return void Executes the specified method on the specified controller with the provided parameters.
     *
     * @throws Exception If the controller class or method does not exist.
     */
    private function invoke($callback, $params) {
        list($controllerName, $methodName) = explode('@', $callback);
        $controllerClass = "../pages/$controllerName";

        // ensure the controller class exists
        if (!class_exists($controllerClass)) {
            throw new Exception("Controller '$controllerClass' not found.");
        }

        $controller = new $controllerClass();

        // ensure the method exists on the controller
        if (!method_exists($controller, $methodName)) {
            throw new Exception("Method '$methodName' not found in controller '$controllerClass'.");
        }

        // call the controller's method with the parameters
        call_user_func_array([$controller, $methodName], $params);
    }

}

?>
