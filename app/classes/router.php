<?php

class Router {

    private $routes = [];

    public function add() {
        $this->routes[$pattern] = $callback;
    }

    public function dispatch($url) {
        // remove variables from url
        $url = strtok($url, '?');

        foreach ($this->routes as $pattern => $callback) {
            if (preg_match('#^' . $pattern . '$#', $url, $matches)) {
                // move any exact match
                array_shift($matches);
                return $this->invoke($callback, $matches);
            }
        }

        // if there was no match at all, return 404
        http_response_code(404);
        echo '404 page not found';
    }

    private function invoke($callback, $params) {
        list($controllerName, $methodName) = explode('@', $callback);
//        $controllerClass = "\\App\\Controllers\\$controllerName";
        $controllerClass = "../pages/$pageName";
        $controller = new $controllerClass();
        call_user_func_array([$controller, $methodName], $params);
    }

}

?>
