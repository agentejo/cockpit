<?php

$app['app.assets.base'] = [
    'assets:vendor/promise.js',
    'assets:vendor/jquery.js',
    'assets:vendor/angular.js',
    'assets:vendor/storage.js',
    'assets:vendor/i18n.js',

    // UIkit
    'cockpit:assets/css/uikit.cockpit.min.css',
    'assets:vendor/uikit/js/uikit.min.js',
    'assets:vendor/uikit/js/addons/notify.min.js'
];


// API

$this->module("cockpit")->extend([

    "assets" => function($assets, $key=null, $cache=0, $cache_folder=null) use($app) {

        $key          = $key ? $key : md5(serialize($assets));
        $cache_folder = $cache_folder ? $cache_folder : $app->path("cache:assets");

        $app("assets")->style_and_script($assets, $key, $cache_folder, $cache);
    },

    "markdown" => function($content) use($app) {

        return \Parsedown::instance()->parse($content);
    },

    "get_registry" => function($key, $default=null) use($app) {

        return $app->memory->hget("cockpit.api.registry", $key, $default);
    }
]);

if (!function_exists('assets')) {

    function assets($assets, $key=null, $cache=0, $cache_folder=null) {
        cockpit("cockpit")->assets($assets, $key, $cache, $cache_folder);
    }
}

if (!function_exists('get_registry')) {

    function get_registry($key, $default=null) {
        return cockpit("cockpit")->get_registry($key, $default);
    }
}

if (!function_exists('markdown')) {

    function markdown($content) {
        echo cockpit("cockpit")->markdown($content);
    }
}

if (!function_exists('url_to')) {

    function url_to($path) {
        echo cockpit()->pathToUrl($content);
    }

    function get_url_to($path) {
        return cockpit()->pathToUrl($content);
    }
}


// extend lexy parser
$app->renderer()->extend(function($content){

    $content = preg_replace('/(\s*)@markdown\((.+?)\)/', '$1<?php echo \Parsedown::instance()->parse($2); ?>', $content);
    $content = preg_replace('/(\s*)@assets\((.+?)\)/' , '$1<?php $app("assets")->style_and_script($2); ?>', $content);

    return $content;
});

// Admin

if (COCKPIT_ADMIN && !COCKPIT_REST) {

    $app["cockpit"] = json_decode($app->helper("fs")->read("#root:package.json"), true);

    $assets = array_merge([
        'assets:vendor/uikit/js/addons/autocomplete.min.js',
        'assets:vendor/uikit/js/addons/search.min.js',
        'assets:vendor/uikit/js/addons/form-select.min.js',
        'assets:vendor/multipleselect.js',
        'cockpit:assets/js/app.js',
        'cockpit:assets/js/app.module.js',
        'cockpit:assets/js/bootstrap.js',
    ], $app->retrieve('app.config/app.assets.backend', []));

    $app['app.assets.backend'] = $assets;

    // helpers
    $app->helpers["admin"]    = 'Cockpit\\Helper\\Admin';
    $app->helpers["versions"] = 'Cockpit\\Helper\\Versions';
    $app->helpers["backup"]   = 'Cockpit\\Helper\\Backup';
    $app->helpers["history"]  = 'Cockpit\\Helper\\HistoryLogger';

    $app->bind("/", function() use($app){
        return $app->invoke("Cockpit\\Controller\\Base", "dashboard");
    });

    $app->bind("/dashboard", function() use($app){
        return $app->invoke("Cockpit\\Controller\\Base", "dashboard");
    });

    $app->bind("/settingspage", function() use($app){
        return $app->invoke("Cockpit\\Controller\\Base", "settings");
    });

    $app->bindClass("Cockpit\\Controller\\Settings", "settings");
    $app->bindClass("Cockpit\\Controller\\Backups", "backups");

    //global search
    $app->bind("/cockpit-globalsearch", function() use($app){

        $query = $app->param("search", false);
        $list  = new \ArrayObject([]);

        if($query) {
            $app->trigger("cockpit.globalsearch", [$query, $list]);
        }

        return json_encode(["results"=>$list->getArrayCopy()]);
    });

    // dashboard widgets
    $app->on("admin.dashboard.main", function() use($app){
        $title = $app("i18n")->get("Today");
        echo $app->view("cockpit:views/dashboard/datetime.php with cockpit:views/layouts/dashboard.widget.php", compact('title'));
    }, 100);

    $app->on("admin.dashboard.main", function() use($app){
        echo $app->view("cockpit:views/dashboard/history.php", ['history' => $app("history")->load()]);
    }, 5);

    // init admin menus
    $app['admin.menu.top']      = new \PriorityQueue();
    $app['admin.menu.dropdown'] = new \PriorityQueue();

    // load i18n definition

    if($user = $app("session")->read('cockpit.app.auth', null)) {
        $app("i18n")->locale = isset($user['i18n']) ? $user['i18n'] : $app("i18n")->locale;
    }

    $locale = $app("i18n")->locale;

    $app("i18n")->load("cockpit:i18n/{$locale}.php", $locale);

    $app->bind("/i18n.js", function() use($app, $locale){

        $app->response->mime = "js";

        $data = $app("i18n")->data($locale);

        return 'if(i18n) { i18n.register('.(count($data) ? json_encode($data):'{}').'); }';
    });


    // acl
    $app("acl")->addResource("Cockpit", ['manage.backups']);
}