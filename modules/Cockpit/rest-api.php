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
        $output     = false;

        if (isset($routes[$resource])) {

            try {

                // invoke class
                if (is_string($routes[$resource])) {

                    $action = count($params) ? array_shift($params):'index';
                    $output = $this->invoke($routes[$resource], $action, $params);

                } elseif (is_callable($routes[$resource])) {
                    $output = call_user_func_array($routes[$resource], $params);
                }

            } catch(Exception $e) {

                $output = ["error" => true];

                $this->response->status = 406;
                $this->trigger('cockpit.api.erroronrequest', [$route, $e->getMessage()]);

                if ($this['debug']) {
                    $output['message'] = $e->getMessage();
                } else {
                    $output['message'] = 'Oooops, something went wrong.';
                }
            }
        }

        if (is_object($output) || is_array($output)) {
            $this->response->mime = 'json';
        }

        return $output;
    });

});
