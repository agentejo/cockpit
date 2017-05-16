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

    $src      = $this->param('src', false);
    $mode     = $this->param('m', 'thumbnail');
    $width    = intval($this->param('w', null));
    $height   = intval($this->param('h', null));
    $quality  = intval($this->param('q', 100));
    $rebuild  = intval($this->param('r', false));
    $base64   = intval($this->param('b64', false));

    if (!$width) {
        return ['error' => 'Target width parameter is missing'];
    }

    if (!$height) {
        return ['error' => 'Target height parameter is missing'];
    }

    if ($src) {

        $src = rawurldecode($src);

        if (!preg_match('/\.(png|jpg|jpeg|gif)/i', $src)) {
            
            if ($asset = $this->storage->findOne("cockpit/assets", ['_id' => $src])) {
                $asset['path'] = trim($asset['path'], '/');
                $src = $this->path("#uploads:{$asset['path']}");
            }
        }

        // check if absolute url
        if (substr($src, 0,1) == '/' && file_exists($this['docs_root'].$src)) {
            $src = $this['docs_root'].$src;
        }

        $options = array(
            "cachefolder" => '#thumbs:',
            "domain"      => false
        );

        extract($options);

        $path  = $this->path($src);
        $ext   = pathinfo($path, PATHINFO_EXTENSION);
        $url   = "data:image/gif;base64,R0lGODlhAQABAJEAAAAAAP///////wAAACH5BAEHAAIALAAAAAABAAEAAAICVAEAOw=="; // transparent 1px gif

        if (!file_exists($path) || is_dir($path)) {
            return false;
        }

        if (!in_array(strtolower($ext), array('png','jpg','jpeg','gif'))) {
            return $url;
        }

        if (is_null($width) && is_null($height)) {
            return $this->pathToUrl($path);
        }

        if (!in_array($mode, ['thumbnail', 'best_fit', 'resize','fit_to_width'])) {
            $mode = 'thumbnail';
        }

        $method = $mode == 'crop' ? 'thumbnail' : $mode;

        if ($base64) {

            try {
                $data = $this->helper("image")->take($path)->{$method}($width, $height)->base64data(null, $quality);
            } catch(Exception $e) {
                return $url;
            }

            $url = $data;

        } else {

            $filetime = filemtime($path);
            $savepath = rtrim($this->path($cachefolder), '/').'/'.md5($path)."_{$width}x{$height}_{$quality}_{$filetime}_{$mode}.{$ext}";

            if ($rebuild || !file_exists($savepath)) {

                try {
                    $this->helper("image")->take($path)->{$method}($width, $height)->toFile($savepath, null, $quality);
                } catch(Exception $e) {
                    return $url;
                }
            }

            $url = $this->pathToUrl($savepath);

            if ($domain) {
                $url = rtrim($this->getSiteUrl(true), '/').$url;
            }

            return $url;
        }
    }

    return false;
});
