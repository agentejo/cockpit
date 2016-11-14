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

        $dirs = ['#cache:','#tmp:','#thumbs:'];

        foreach($dirs as $dir) {

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($app->path($dir)), \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {

                if (!$file->isFile()) continue;
                if (preg_match('/(.gitkeep|index\.html)$/', $file)) continue;

                @unlink($file->getRealPath());
            }

            $app->helper("fs")->removeEmptySubFolders('#cache:');
        }

        $app->trigger("cockpit.clearcache");

        $size = 0;

        foreach($dirs as $dir) {
            $size += $app->helper("fs")->getDirSize($dir);
        }

        return ["size"=>$app->helper("utils")->formatSize($size)];
    },

    "loadApiKeys" => function() {

        $keys      = [ 'master' => '', 'special' => [] ];
        $container = $this->app->path('#storage:').'/api.keys.php';

        if (file_exists($container)) {
            $data = include($container);
            $data = unserialize($this->app->decode($data, $this->app["sec-key"]));

            if ($data !== false) {
                $keys = array_merge($keys, $data);
            }
        }

        return $keys;
    },

    "saveApiKeys" => function($data) {

        $data      = serialize(array_merge([ 'master' => '', 'special' => [] ], (array)$data));
        $export    = var_export($this->app->encode($data, $this->app["sec-key"]), true);
        $container = $this->app->path('#storage:').'/api.keys.php';

        return $this->app->helper('fs')->write($container, "<?php\n return {$export};");
    }

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
            "user"   => $data["user"],
            "active" => true
        ]);

        if (count($user) && password_verify($data["password"], $user["password"])) {

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
            if ($app("acl")->hasaccess($user["group"], $resource, $action)) return true;
        }

        return false;
    },

    "getGroup" => function() use($app) {

        $user = $this->getUser();

        if (isset($user["group"])) {
            return $user["group"];
        }

        return false;
    },

    "getGroupRights" => function($resource, $group = null) use($app) {

        if ($group) {
            return $app("acl")->getGroupRights($group, $resource);
        }

        $user = $this->getUser();

        if (isset($user["group"])) {
            return $app("acl")->getGroupRights($user["group"], $resource);
        }

        return false;
    },

    "isSuperAdmin" => function($group = null) use($app) {

        if (!$group) {

            $user = $this->getUser();

            if (isset($user["group"])) {
                $group = $user["group"];
            }
        }

        return $group ? $app("acl")->isSuperAdmin($group) : false;
    },

    "getGroups" => function() use($app) {

        $groups = array_merge(['admin'], array_keys($app->retrieve("config/groups", [])));

        return array_unique($groups);
    },

    "getGroupVar" => function($setting, $default = null) use($app) {

        if ($user = $this->getUser()) {

            if (isset($user['group']) && $user['group']) {

                return $app('acl')->getVar($user['group'], $setting, $default);
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

// REST
if (COCKPIT_REST) {

    // INIT REST API HANDLER
    include_once(__DIR__.'/rest-api.php');

    $this->on('cockpit.rest.init', function($routes) {
        $routes['cockpit'] = 'Cockpit\\Controller\\RestApi';
    });
}

if (COCKPIT_ADMIN) {

    $this->bind("/api.js", function() {

        $token                = $this->param("token", "");
        $this->response->mime = 'js';

        return $this->view('cockpit:views/api.js', compact('token'));
    });
}


// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_REST) {

    include_once(__DIR__.'/admin.php');
}


// WEBHOOKS
include_once(__DIR__.'/webhooks.php');
