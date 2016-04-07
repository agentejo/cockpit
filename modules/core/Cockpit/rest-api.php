<?php


$this->on("before", function() {

    $routes = new \ArrayObject([]);

    /*
        $routes['{:resource}'] = string (classname) | callable
    */
    $this->trigger("cockpit.rest.init", [$routes])->bind("/api/*", function($params) use($routes) {

        $route = $this['route'];
        $path  = $params[":splat"][0];

        if (!$path) {
            return false;
        }

        // api key check
        $apikeys = $this->module('cockpit')->loadApiKeys();
        $allowed = (isset($apikeys['master']) && trim($apikeys['master']) && $apikeys['master'] == $this->param('token'));

        if (!$allowed) {
            return false;
        }

        $parts      = explode('/', $path, 2);
        $resource   = $parts[0];
        $params     = isset($parts[1]) ? explode('/', $parts[1]) : [];

        if (isset($routes[$resource])) {

            // invoke class
            if (is_string($routes[$resource])) {
                $action = count($params) ? array_shift($params):'index';
                return $this->invoke($routes[$resource], $action, $params);
            }

            if (is_callable($routes[$resource])) {
                return call_user_func_array($routes[$resource], $params);
            }
        }

        return false;
    });

});
