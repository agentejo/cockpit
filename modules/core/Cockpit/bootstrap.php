<?php

$app['app.assets.base'] = [
    'assets:vendor/polyfills/es-shim.js',
    'assets:vendor/jquery.js',
    'assets:vendor/storage.js',
    'assets:vendor/i18n.js',

    // UIkit
    'assets:css/cockpit.css',
    'assets:vendor/uikit/js/uikit.min.js',
    'assets:vendor/uikit/js/components/notify.min.js'
];


// API

$this->module("cockpit")->extend([

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

    "get_registry" => function($key, $default=null) use($app) {

        return $app->memory->hget("cockpit.api.registry", $key, $default);
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

    function get_markdown($content, $extra = false) {
        return cockpit("cockpit")->markdown($content, $extra);
    }

    function markdown($content, $extra = false) {
        echo cockpit("cockpit")->markdown($content, $extra);
    }
}

if (!function_exists('url_to')) {

    function url_to($path) {
        echo cockpit()->pathToUrl($path);
    }

    function get_url_to($path) {
        return cockpit()->pathToUrl($path);
    }
}

if (!function_exists('path_to')) {

    function path_to($path) {
        echo cockpit()->path($path);
    }

    function get_path_to($path) {
        return cockpit()->path($path);
    }
}

// REST
$app->on('cockpit.rest.init', function($routes) {
    $routes["cockpit"] = 'Cockpit\\Controller\\RestApi';
});


// extend lexy parser
$app->renderer->extend(function($content){

    $content = preg_replace('/(\s*)@markdown\((.+?)\)/', '$1<?php echo \Parsedown::instance()->parse($2); ?>', $content);
    $content = preg_replace('/(\s*)@assets\((.+?)\)/' , '$1<?php $app("assets")->style_and_script($2); ?>', $content);

    return $content;
});

// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_REST) include_once(__DIR__.'/admin.php');
