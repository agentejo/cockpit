<?php


$this->on("before", function() {

    $routes = new \ArrayObject([]);

    /*
        $routes['{:resource}'] = string (classname) | callable
    */
    $this->trigger("cockpit.rest.init", [$routes])->bind("/api/*", function($params) use($routes) {

        $this->module('cockpit')->setUser(false, false);

        $route = $this['route'];
        $path  = $params[":splat"][0];

        if (!$path) {
            return false;
        }

        $token = $this->param('token');

        // api key check
        #
        $allowed = false;

        if (preg_match('/account-/', $token)) {
            
            $account = $this->storage->findOne("cockpit/accounts", ["api_key" => $token]);

            if ($account) {
                $allowed = true;
                $this->module('cockpit')->setUser($account, false);
            }

        } else {
            $apikeys = $this->module('cockpit')->loadApiKeys();
            $allowed = (isset($apikeys['master']) && trim($apikeys['master']) && $apikeys['master'] == $token);
        }
        
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
