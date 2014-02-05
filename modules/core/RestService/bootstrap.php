<?php


$app->on("before", function() use($app) {

    $routes = new \ArrayObject([]);

    /*
        $routes['{:resource}'] = string (classname) | callable 

    */

    $app->trigger("cockpit.rest.init", [$routes])->bind("/rest/api/*", function($params) use($routes){
        $path = $params[":splat"][0];

        if(!$params[":splat"][0]) {
            return false;
        }

        $parts      = explode('/', $params[":splat"][0], 2);
        $resource   = $parts[0];
        $params     = isset($parts[1]) ? explode('/', $parts[1]) : [];

        if(isset($routes[$resource])) {

            // invoke class
            if(is_string($routes[$resource])) {

                $action = count($params) ? array_shift($params):'index';

                return $app->invoke($routes[$resource], $action, $params);
            }

            if(is_callable($routes[$resource])) {
                return call_user_func_array($routes[$resource], $params);
            }
        }

        return false;
    });

});