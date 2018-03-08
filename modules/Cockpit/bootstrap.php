<?php

// Helpers

$this->helpers['revisions']  = 'Cockpit\\Helper\\Revisions';
$this->helpers['updater']  = 'Cockpit\\Helper\\Updater';

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
                if (preg_match('/(\.gitkeep|\.gitignore|index\.html)$/', $file)) continue;

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
    },

    "thumbnail" => function($options) {

        $options = array_merge(array(
            'cachefolder' => '#thumbs:',
            'src' => '',
            'mode' => 'thumbnail',
            'fp' => null,
            'filter' => '',
            'width' => false,
            'height' => false,
            'quality' => 100,
            'rebuild' => false,
            'base64' => false,
            'output' => false,
            'domain' => false
        ), $options);

        extract($options);

        if (!$width && !$height) {
            return ['error' => 'Target width or height parameter is missing'];
        }

        if (!$src) {
            return ['error' => 'Missing src parameter'];
        }

        $src = str_replace('../', '', rawurldecode($src));

        if (!preg_match('/\.(png|jpg|jpeg|gif)$/i', $src)) {

            if ($asset = $this->app->storage->findOne("cockpit/assets", ['_id' => $src])) {
                $asset['path'] = trim($asset['path'], '/');
                $src = $this->app->path("#uploads:{$asset['path']}");

                if ($src) {
                    $src = str_replace(COCKPIT_SITE_DIR, '', $src);
                }

                if (isset($asset['fp']) && !$fp) {
                    $fp = $asset['fp']['x'].' '.$asset['fp']['y'];
                }

            }
        }

        if ($src) {

            $src = ltrim($src, '/');

            if (file_exists(COCKPIT_SITE_DIR.'/'.$src)) {
                $src = COCKPIT_SITE_DIR.'/'.$src;
            } elseif (file_exists(COCKPIT_DOCS_ROOT.'/'.$src)) {
                $src = COCKPIT_DOCS_ROOT.'/'.$src;
            }
        }

        $path  = $this->app->path($src);
        $ext   = pathinfo($path, PATHINFO_EXTENSION);
        $url   = "data:image/gif;base64,R0lGODlhAQABAJEAAAAAAP///////wAAACH5BAEHAAIALAAAAAABAAEAAAICVAEAOw=="; // transparent 1px gif

        if (!file_exists($path) || is_dir($path)) {
            return false;
        }

        if (!in_array(strtolower($ext), array('png','jpg','jpeg','gif'))) {
            return $url;
        }

        if (!$width || !$height) {

            list($w, $h, $type, $attr)  = getimagesize($path);

            if (!$width) $width = ceil($w * ($height/$h));
            if (!$height) $height = ceil($h * ($width/$w));
        }

        if (is_null($width) && is_null($height)) {
            return $this->app->pathToUrl($path);
        }

        if (!$fp) {
            $fp = 'center';
        }

        if (!in_array($mode, ['thumbnail', 'bestFit', 'resize','fitToWidth','fitToHeight'])) {
            $mode = 'thumbnail';
        }

        $method = $mode == 'crop' ? 'thumbnail' : $mode;

        $filetime = filemtime($path);
        $hash = md5($path.json_encode($options))."_{$width}x{$height}_{$quality}_{$filetime}_{$mode}_".md5($fp).".{$ext}";
        $savepath = rtrim($this->app->path($cachefolder), '/')."/{$hash}";

        if ($rebuild || !file_exists($savepath)) {

            try {

                $img = $this->app->helper("image")->take($path)->{$method}($width, $height, $fp);

                $_filters = [
                    'blur', 'brighten',
                    'colorize', 'contrast',
                    'darken', 'desaturate',
                    'edge detect', 'emboss',
                    'flip', 'invert', 'opacity', 'pixelate', 'sepia', 'sharpen', 'sketch'
                ];

                foreach($_filters as $f) {

                    if (isset($options[$f])) {
                        $img->{$f}($options[$f]);
                    }
                }

                $img->toFile($savepath, null, $quality);
            } catch(Exception $e) {
                return $url;
            }
        }

        if ($base64) {
            return "data:image/{$ext};base64,".base64_encode(file_get_contents($savepath));
        }

        if ($output) {
            header("Content-Type: image/{$ext}");
            header('Content-Length: '.filesize($savepath));
            readfile($savepath);
            $this->app->stop();
        }

        $url = $this->app->pathToUrl($savepath);

        if ($domain) {

            $_url = ($this->app->req_is('ssl') ? 'https':'http').'://';

            if (!in_array($this->app['base_port'], ['80', '443'])) {
                $_url .= $this->app['base_host'].":".$this->app['base_port'];
            } else {
                $_url .= $this->app['base_host'];
            }

            $url = rtrim($_url, '/').$url;
        }

        return $url;
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

    "setUser" => function($user, $permanent = true) use($app) {

        if ($permanent) {
            $app("session")->write('cockpit.app.auth', $user);
        }

        $app['cockpit.auth.user'] = $user;
    },

    "getUser" => function($prop = null, $default = null) use($app) {

        $user = $app->retrieve('cockpit.auth.user');

        if (is_null($user)) {
            $user = $app("session")->read('cockpit.app.auth', null);
        }

        if (!is_null($prop)) {
            return $user && isset($user[$prop]) ? $user[$prop] : $default;
        }

        return $user;
    },

    "logout" => function() use($app) {
        $app("session")->delete('cockpit.app.auth');
    },

    "hasaccess" => function($resource, $action, $group = null) use($app) {

        if (!$group) {
            $user = $this->getUser();
            $group = isset($user["group"]) ? $user["group"] : null;
        }

        if ($group) {
            if ($app("acl")->hasaccess($group, $resource, $action)) return true;
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

// ACL
$app('acl')->addResource('cockpit', [
    'backend', 'finder',
]);


// init acl groups + permissions + settings

$app('acl')->addGroup('admin', true);

/*
groups:
    author:
        $admin: false
        $vars:
            finder.path: /upload
        cockpit:
            backend: true
            finder: true

*/

$aclsettings = $app->retrieve('config/groups', []);

foreach ($aclsettings as $group => $settings) {

    $isSuperAdmin = $settings === true || (isset($settings['$admin']) && $settings['$admin']);
    $vars         = isset($settings['$vars']) ? $settings['$vars'] : [];

    $app('acl')->addGroup($group, $isSuperAdmin, $vars);

    if (!$isSuperAdmin && is_array($settings)) {

        foreach ($settings as $resource => $actions) {

            if ($resource == '$vars' || $resource == '$admin') continue;

            foreach ((array)$actions as $action => $allow) {
                if ($allow) {
                    $app('acl')->allow($group, $resource, $action);
                }
            }
        }
    }
}


// REST
if (COCKPIT_API_REQUEST) {

    // INIT REST API HANDLER
    include_once(__DIR__.'/rest-api.php');

    $this->on('cockpit.rest.init', function($routes) {
        $routes['cockpit'] = 'Cockpit\\Controller\\RestApi';
    });
}

if (COCKPIT_ADMIN) {

    $this->bind("/api.js", function() {

        $token                = $this->param('token', '');
        $this->response->mime = 'js';

        $apiurl = ($this->req_is('ssl') ? 'https':'http').'://';

        if (!in_array($this->registry['base_port'], ['80', '443'])) {
            $apiurl .= $this->registry['base_host'].":".$this->registry['base_port'];
        } else {
            $apiurl .= $this->registry['base_host'];
        }

        $apiurl .= $this->routeUrl('/api');

        return $this->view('cockpit:views/api.js', compact('token', 'apiurl'));
    });
}


// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {

    include_once(__DIR__.'/admin.php');
}


// WEBHOOKS
if (!defined('COCKPIT_INSTALL')) {
    include_once(__DIR__.'/webhooks.php');
}
