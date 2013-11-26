<?php

$app['app.assets.base'] = [
    'assets:vendor/jquery.js',
    'assets:vendor/angular.js',
    'assets:vendor/storage.js',
    'assets:vendor/uikit/js/uikit.min.js',
    'assets:vendor/uikit/css/uikit.min.css',
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

    $app->on("admin.dashboard", function() use($app){

        $title = "Today";

        echo $app->view("cockpit:views/dashboard/datetime.php with cockpit:views/layouts/dashboard.widget.php", compact('title'));
    });

    $app['admin.menu.top']      = new \PriorityQueue();
    $app['admin.menu.dropdown'] = new \PriorityQueue();
}