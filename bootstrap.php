<?php

if(!defined('COCKPIT_ADMIN')) {
    define('COCKPIT_ADMIN', 0);
}

// autoload from vendor
spl_autoload_register(function($class){
    $class_path = __DIR__.'/vendor/'.str_replace('\\', '/', $class).'.php';
    if(file_exists($class_path)) include_once($class_path);
});


function cockpit($module = null) {

    static $app;

    if(!$app) {

        $config              = include(__DIR__.'/config.php');
        $app                 = new LimeExtra\App($config);

        $app["app.config"]   = $config;

        $app->path('data'    , __DIR__.'/storage/data');
        $app->path('cache'   , __DIR__.'/storage/cache');
        $app->path('modules' , __DIR__.'/modules');
        $app->path('assets'  , __DIR__.'/assets');
        $app->path('site'    , dirname(__DIR__));

        $app->service('data', function() use($app) {

            $client = new MongoLite\Client($app->path('data:'));

            return $client;
        });

        $app->service('memory', function() use($app) {

            $client = new RedisLite(sprintf("%s/cockpit.memory.sqlite", $app->path('data:')));

            return $client;
        });

        // mailer service
        $app->service("mailer", function() use($app){

            $options   = $app->retrieve("app.config/mailer", []);
            $mailer    = new \Mailer(isset($options["transport"]) ? $options["transport"]:"mail", $options);

            return $mailer;
        });

        // i18n

        $app("i18n")->locale = isset($config["i18n"]) ? $config["i18n"]:"en";

        $app->loadModules(__DIR__.'/modules/core');
        $app->loadModules(__DIR__.'/modules/addons');
    }

    return $module ? $app->module($module) : $app;
}

$cockpit = cockpit();