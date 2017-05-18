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

        $token = $this->param('token', isset($_SERVER['HTTP_COCKPIT_TOKEN']) ? $_SERVER['HTTP_COCKPIT_TOKEN'] : null);

        // api key check
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

        $parts      = explode('/', $path, 2);
        $resource   = $parts[0];
        $params     = isset($parts[1]) ? explode('/', $parts[1]) : [];
        $output     = false;
        $user       = $this->module('cockpit')->getUser();

        if ($resourcefile = $this->path("#config:api/public/{$resource}.php")) {
            
            $output = include($resourcefile);

        } elseif ($allowed && $resourcefile = $this->path("#config:api/{$resource}.php")) {

            $output = include($resourcefile);

        } elseif ($allowed && isset($routes[$resource])) {

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


$this->bind('/api/image', function() {

    $options = [
        'src' => $this->param('src', false),
        'mode' => $this->param('m', 'thumbnail'),
        'width' => intval($this->param('w', null)),
        'height' => intval($this->param('h', null)),
        'quality' => intval($this->param('q', 100)),
        'rebuild' => intval($this->param('r', false)),
        'base64' => intval($this->param('b64', false)),
        'output' => intval($this->param('o', false)),
        'domain' => intval($this->param('d', false)),
    ];

    foreach([
        'blur', 'brighten', 
        'colorize', 'contrast', 
        'darken', 'desaturate', 
        'edge detect', 'emboss', 
        'flip', 'invert', 'opacity', 'pixelate', 'sepia', 'sharpen', 'sketch'
    ] as $f) {
        if ($this->param($f)) $options[$f] = $this->param($f);
    }

    return $this->module('cockpit')->thumbnail($options);
});
