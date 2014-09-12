<?php

// API

$this->module("restservice")->extend([

    'js_lib' => function($token = null) use($app) {

        return $app->script($app->routeUrl("/rest/api-js?token={$token}"));
    }
]);


if (!function_exists("cockpit_js_lib")) {
    function cockpit_js_lib($token = null) {
        echo cockpit("restservice")->js_lib($token);
    }
}

$app->on("before", function() {

    $routes = new \ArrayObject([]);

    /*
        $routes['{:resource}'] = string (classname) | callable
    */

    $this->trigger("cockpit.rest.init", [$routes])->bind("/rest/api/*", function($params) use($routes){

        $route = $this['route'];
        $token = $this->param("token", false);
        $path  = $params[":splat"][0];

        if (!$token || !$params[":splat"][0]) {
            return false;
        }

        $tokens = $this->db->getKey("cockpit/settings", "cockpit.api.tokens", []);

        if (!isset($tokens[$token])) {
            $this->response->status = 401;
            return ["error" => "access denied"];
        }


        // rules validation
        $rules = trim(preg_replace('/#(.+)/', '', $tokens[$token])); // trim and replace comments
        $pass  = false;

        if ($rules == '') {
            $pass = true;
        } else {

            $lines = explode("\n", $rules);

            // validate every rule
            foreach ($lines as $rule) {

                $rule = trim($rule);

                if (!$rule) continue;

                $ret  = $rule[0] == '!' ? false : true;

                if (!$ret) {
                    $rule = substr($rule, 1);
                }

                if (preg_match("#{$rule}#", $route)) {
                    $pass = $ret;
                    break;
                }
            }
        }

        // deny access
        if (!$pass) {
            $this->response->status = 401;
            return ["error" => "access denied"];
        }

        $parts      = explode('/', $params[":splat"][0], 2);
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

$app->bind("/rest/api-js", function() {

    $token    = $this->param("token", "");
    $registry = json_encode((object)$this->memory->get("cockpit.api.registry", []));

    $this->response->mime = "js";

    return $this->view('restservice:views/api.js', compact('token', 'registry'));
});
