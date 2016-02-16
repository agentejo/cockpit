<?php

// API

$this->module("cockpit")->extend([

    // General Api

    "assets" => function($assets, $key=null, $cache=0, $cache_folder=null) use($app) {

        $key          = $key ? $key : md5(serialize($assets));
        $cache_folder = $cache_folder ? $cache_folder : $app->path("cache:assets");

        $app("assets")->style_and_script($assets, $key, $cache_folder, $cache);
    },

    "markdown" => function($content, $extra = false) use($app) {

        static $parseDown;
        static $parsedownExtra;

        if (!$extra && !$parseDown)      $parseDown      = new \Parsedown();
        if ($extra && !$parsedownExtra)  $parsedownExtra = new \ParsedownExtra();

        return $extra ? $parsedownExtra->text($content) : $parseDown->text($content);
    },

    "clearCache" => function() use($app) {

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($app->path("cache:")), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file) {

            if (!$file->isFile()) continue;
            if (preg_match('/(.gitkeep|index\.html)$/', $file)) continue;

            @unlink($file->getRealPath());
        }

        $app->helper("fs")->removeEmptySubFolders('cache:');
        $app->trigger("cockpit.clearcache");

        return ["size"=>$app->helper("utils")->formatSize($app->helper("fs")->getDirSize('cache:'))];
    },


]);


// Auth Api
$this->module("cockpit")->extend([

    "authenticate" => function($data) use($app) {

        $data = array_merge([
            "user"     => "",
            "email"    => "",
            "group"    => "",
            "password" => ""
        ], $data);

        if (!$data["password"]) return false;

        $user = $app->storage->findOne("cockpit/accounts", [
            "user"     => $data["user"],
            "password" => $app->hash($data["password"]),
            "active"   => true
        ]);

        if (count($user)) {

            $user = array_merge($data, (array)$user);

            unset($user["password"]);

            return $user;
        }

        return false;
    },

    "setUser" => function($user) use($app) {
        $app("session")->write('cockpit.app.auth', $user);
    },

    "getUser" => function() use($app) {
        return $app("session")->read('cockpit.app.auth', null);
    },

    "logout" => function() use($app) {
        $app("session")->delete('cockpit.app.auth');
    },

    "hasaccess" => function($resource, $action) use($app) {

        $user = $this->getUser();

        if (isset($user["group"])) {

            if ($user["group"]=='admin') return true;
            if ($app("acl")->hasaccess($user["group"], $resource, $action)) return true;
        }

        return false;
    },

    "getGroups" => function() use($app) {

        $groups = array_merge(['admin'], array_keys($app->retrieve("config/acl", [])));

        return array_unique($groups);
    },

    "getGroupSetting" => function($setting, $default = null) use($app) {

        if ($user = $this->getUser()) {

            if (isset($user["group"]) && $user["group"]) {

                $group = $user["group"];

                return $app->retrieve("config/acl/{$group}/settings/{$setting}", $default);
            }
        }

        return $default;
    },

    "userInGroup" => function($groups) use($app) {

        $user = $this->getUser();

        return (isset($user["group"]) && in_array($user["group"], (array)$groups));
    },

    "updateUserOption" => function($key, $value) use($app) {

        if ($user = $this->getUser()) {

            $data = isset($user['data']) && is_array($user['data']) ? $user['data'] : [];

            $data[$key] = $value;

            $app->storage->update('cockpit/accounts', ['_id' => $user['_id']], ['data' => $data]);

            return $value;
        }

        return false;
    }
]);

// Init REST handler
if (COCKPIT_REST) {

    $this->on("before", function() {

        $routes = new \ArrayObject([]);

        /*
            $routes['{:resource}'] = string (classname) | callable
        */
        $this->trigger("cockpit.rest.init", [$routes])->bind("/rest/api/*", function($params) use($routes) {

            $route = $this['route'];
            $path  = $params[":splat"][0];

            if (!$path) {
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
}

// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_REST) {

    include_once(__DIR__.'/admin.php');
}
