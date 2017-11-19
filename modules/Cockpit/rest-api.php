<?php


$this->on('before', function() {

    $routes = new \ArrayObject([]);

    /*
        $routes['{:resource}'] = string (classname) | callable
    */
    $this->trigger('cockpit.rest.init', [$routes])->bind('/api/*', function($params) use($routes) {

        $this->module('cockpit')->setUser(false, false);

        $route = $this['route'];
        $path  = $params[':splat'][0];

        if (!$path) {
            return false;
        }

        $token = $this->param('token', isset($_SERVER['HTTP_COCKPIT_TOKEN']) ? $_SERVER['HTTP_COCKPIT_TOKEN'] : null);

        // api key check
        $allowed = false;

        if (preg_match('/account-/', $token)) {

            $account = $this->storage->findOne('cockpit/accounts', ['api_key' => $token]);

            if ($account) {
                $allowed = true;
                $this->module('cockpit')->setUser($account, false);
            }

        } elseif ($token) {

            $apikeys = $this->module('cockpit')->loadApiKeys();

            // check master for master key
            $allowed = (isset($apikeys['master']) && trim($apikeys['master']) && $apikeys['master'] == $token);

            if (!$allowed && count($apikeys['special'])) {

                foreach ($apikeys['special'] as &$apikey) {

                    if ($apikey['token'] == $token) {

                        $rules =  trim($apikey['rules']);

                        if ($rules == '*') {
                            $allowed = true;
                            break;
                        }

                        foreach(explode("\n", $rules) as $rule) {

                            $rule = trim($rule);
                            if (!$rule) continue;

                            if (preg_match("#{$rule}#", COCKPIT_ADMIN_ROUTE)) {
                                $allowed = true;
                                break;
                            }
                        }

                        break;
                    }
                }
            }

        }

        $parts      = explode('/', $path, 2);
        $resource   = $parts[0];
        $params     = isset($parts[1]) ? explode('/', $parts[1]) : [];

        // trigger authenticate event
        if (!$allowed) {

            $data = new ArrayObject([
                'token' => $token,
                'authenticated' => false,
                'resource' => $resource,
                'query' => ['path' => $path, 'parts' => $parts, 'params' => $params],
                'user'=>null,
            ]);

            $this->trigger('cockpit.api.authenticate', [$data]);

            $allowed = $data['authenticated'];

            if ($data['user']) {
                $this->module('cockpit')->setUser($data['user'], false);
            }
        }

        $output = false;
        $user   = $this->module('cockpit')->getUser();

        if ($resource == 'public' && $resourcefile = $this->path("#config:api/{$path}.php")) {
            
            $output = include($resourcefile);

        } elseif ($allowed && $resourcefile = $this->path("#config:api/{$path}.php")) {

            $output = include($resourcefile);

        } elseif ($allowed && isset($routes[$resource])) {

            try {

                // invoke class
                if (is_string($routes[$resource])) {

                    $action = count($params) ? array_shift($params) : 'index';
                    $output = $this->invoke($routes[$resource], $action, $params);

                } elseif (is_callable($routes[$resource])) {
                    $output = call_user_func_array($routes[$resource], $params);
                }

            } catch(Exception $e) {

                $output = ['error' => true];

                $this->response->status = 406;
                $this->trigger('cockpit.api.erroronrequest', [$route, $e->getMessage()]);

                if ($this['debug']) {
                    $output['message'] = $e->getMessage();
                } else {
                    $output['message'] = 'Oooops, something went wrong.';
                }
            }
        }

        if ($output === false && !$allowed) {
            $this->response->mime = 'json';
            $this->stop(401);
        }

        if (is_object($output) || is_array($output)) {
            $this->response->mime = 'json';
        }

        return $output;
    });

});
