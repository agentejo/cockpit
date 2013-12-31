<?php

$app['app.assets.base'] = [
    'assets:vendor/jquery.js',
    'assets:vendor/angular.js',
    'assets:vendor/storage.js',
    'assets:vendor/i18n.js',
    'assets:vendor/uikit/js/uikit.min.js',
    'assets:vendor/uikit/css/uikit.min.css',
    'assets:vendor/uikit/addons/js/notify.min.js',
    'assets:vendor/uikit/addons/css/notify.min.css',
    'assets:vendor/modalbox/modalbox.css',
    'assets:vendor/modalbox/modalbox.js'
];


// API

$this->module("cockpit")->assets = function($assets, $key=null, $cache=0, $cache_folder=null) use($app) {

    $key          = $key ? $key : md5(serialize($assets));
    $cache_folder = $cache_folder ? $cache_folder : $app->path("cache:assets");

    $app("assets")->style_and_script($assets, $key, $cache_folder, $cache);
};

if (!function_exists('assets')) {

    function assets($assets, $key=null, $cache=0, $cache_folder=null) {
        cockpit("cockpit")->assets($assets, $key, $cache, $cache_folder);
    }
}

$this->module("cockpit")->markdown = function($content) use($app) {

    return \Parsedown::instance()->parse($content);
};

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

// Admin

if (COCKPIT_ADMIN) {

    $app['app.assets.backend'] = [
        'cockpit:assets/js/app.js',
        'cockpit:assets/js/app.module.js',
        'cockpit:assets/css/app.less',
        'cockpit:assets/js/bootstrap.js',
    ];

    $app->helpers["admin"] = 'Cockpit\\Helper\\Admin';

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

    $app->bind("/profile/:id", function($params) use($app){
        return $app->invoke("Cockpit\\Controller\\Base", "profile");
    });

    $app->bindClass("Cockpit\\Controller\\Accounts", "accounts");

    $app->on("admin.dashboard", function() use($app){

        $title = "Today";

        echo $app->view("cockpit:views/dashboard/datetime.php with cockpit:views/layouts/dashboard.widget.php", compact('title'));
    });

    $app['admin.menu.top']      = new \PriorityQueue();
    $app['admin.menu.dropdown'] = new \PriorityQueue();

    $locale = $app("i18n")->locale;

    $app("i18n")->load("cockpit:i18n/{$locale}.php", $locale);
}