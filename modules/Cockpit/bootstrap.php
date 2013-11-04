<?php

$app['app.assets.base'] = [
    'assets:vendor/jquery.js',
    'assets:vendor/angular.js',
    'assets:vendor/storage.js',
    'assets:vendor/uikit/js/uikit.min.js',
    'assets:vendor/uikit/css/uikit.min.css',
    'assets:vendor/modalbox/modalbox.css',
    'assets:vendor/modalbox/modalbox.js',
    'assets:vendor/uikit/addons/css/form-icon.min.css'
];

$app['app.assets.backend'] = [
    'cockpit:assets/js/app.js',
    'cockpit:assets/js/app.module.js',
    'cockpit:assets/css/app.less',
    'cockpit:assets/js/bootstrap.js',
];

if (COCKPIT_ADMIN) {

    $app->helpers["admin"] = 'Cockpit\\Helper\\Admin';

    $app->bind("/", function() use($app){
        return $app->invoke("Cockpit\\Controller\\Base", "dashboard");
    });

    $app->bind("/dashboard", function() use($app){
        return $app->invoke("Cockpit\\Controller\\Base", "dashboard");
    });

    $app->bind("/settings", function() use($app){
        return $app->invoke("Cockpit\\Controller\\Base", "settings");
    });

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