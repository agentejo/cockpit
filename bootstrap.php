<?php

// autoload from vendor (PSR-0)
spl_autoload_register(function($class){
    $class_path = __DIR__.'/vendor/'.str_replace('\\', '/', $class).'.php';
    if(file_exists($class_path)) include_once($class_path);
});

if (!defined('COCKPIT_ADMIN')) {
    define('COCKPIT_ADMIN', 0);
}

if (!defined('COCKPIT_REST')) {
    define('COCKPIT_REST', COCKPIT_ADMIN && isset($_SERVER["PATH_INFO"]) && strpos($_SERVER["PATH_INFO"], '/rest/api')===0 ? 1:0);
}


function cockpit($module = null) {

    static $app;

    if(!$app) {

        $config = include(__DIR__.'/config.php');

        if(file_exists(__DIR__.'/custom/config.php')) {
            $config = array_merge($config, include(__DIR__.'/custom/config.php'));
        }

        $app = new LimeExtra\App($config);

        $app["app.config"] = $config;

        $app->path('#root'   , __DIR__);
        $app->path('storage' , __DIR__.'/storage');
        $app->path('backups' , __DIR__.'/storage/backups');
        $app->path('data'    , __DIR__.'/storage/data');
        $app->path('cache'   , __DIR__.'/storage/cache');
        $app->path('tmp'     , __DIR__.'/storage/cache/tmp');
        $app->path('modules' , __DIR__.'/modules');
        $app->path('assets'  , __DIR__.'/assets');
        $app->path('custom'  , __DIR__.'/custom');
        $app->path('site'    , dirname(__DIR__));

        // nosql storage
        $app->service('db', function() use($config) {
            $client = new MongoHybrid\Client($config["database"]["server"], $config["database"]["options"]);
            return $client;
        });

        // key-value storage
        $app->service('memory', function() use($app) {
            $client = new RedisLite(sprintf("%s/cockpit.memory.sqlite", $app->path('data:')));
            return $client;
        });

        // mailer service
        $app->service("mailer", function() use($app, $config){

            $options   = isset($config['mailer']) ? $config['mailer']:[];
            $mailer    = new \Mailer(isset($options["transport"]) ? $options["transport"]:"mail", $options);

            return $mailer;
        });

        // set cache path
        $app("cache")->setCachePath("cache:tmp");

        // i18n
        $app("i18n")->locale = isset($config["i18n"]) ? $config["i18n"]:"en";

        // load modules
        $app->loadModules([
            __DIR__.'/modules/core',  # core
            __DIR__.'/modules/addons' # addons
        ]);
    }

    return $module ? $app->module($module) : $app;
}

$cockpit = cockpit();