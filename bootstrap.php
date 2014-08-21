<?php

/*
 * Autoload from vendor (PSR-0)
 */

spl_autoload_register(function($class){
    $class_path = __DIR__.'/vendor/'.str_replace('\\', '/', $class).'.php';
    if(file_exists($class_path)) include_once($class_path);
});


/*
 * Collect needed paths
 */

$COCKPIT_DIR         = str_replace(DIRECTORY_SEPARATOR, '/', __DIR__);
$COCKPIT_DOCS_ROOT   = str_replace(DIRECTORY_SEPARATOR, '/', isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : dirname(__DIR__));

# make sure that $_SERVER['DOCUMENT_ROOT'] is set correctly
if (strpos($COCKPIT_DIR, $COCKPIT_DOCS_ROOT)!==0 && isset($_SERVER['SCRIPT_NAME'])) {
    $COCKPIT_DOCS_ROOT = str_replace(dirname(str_replace(DIRECTORY_SEPARATOR, '/', $_SERVER['SCRIPT_NAME'])), '', $COCKPIT_DIR);
}

$COCKPIT_BASE        = trim(str_replace($COCKPIT_DOCS_ROOT, '', $COCKPIT_DIR), "/");
$COCKPIT_BASE_URL    = strlen($COCKPIT_BASE) ? "/{$COCKPIT_BASE}": $COCKPIT_BASE;
$COCKPIT_BASE_ROUTE  = "{$COCKPIT_BASE_URL}/index.php";

/*
 * SYSTEM DEFINES
 */
if (!defined('COCKPIT_ADMIN'))      define('COCKPIT_ADMIN'      , 0);
if (!defined('COCKPIT_REST'))       define('COCKPIT_REST'       , COCKPIT_ADMIN && isset($_SERVER["PATH_INFO"]) && strpos($_SERVER["PATH_INFO"], '/rest/api')===0 ? 1:0);
if (!defined('COCKPIT_DIR'))        define('COCKPIT_DIR'        , $COCKPIT_DIR);
if (!defined('COCKPIT_DOCS_ROOT'))  define('COCKPIT_DOCS_ROOT'  , $COCKPIT_DOCS_ROOT);
if (!defined('COCKPIT_BASE_URL'))   define('COCKPIT_BASE_URL'   , $COCKPIT_BASE_URL);
if (!defined('COCKPIT_BASE_ROUTE')) define('COCKPIT_BASE_ROUTE' , $COCKPIT_BASE_ROUTE);

function cockpit($module = null) {

    static $app;

    if (!$app) {

        // load config
        $config = array_merge([
            'app.name'     => 'Cockpit',
            'base_url'     => COCKPIT_BASE_URL,
            'base_route'   => COCKPIT_BASE_ROUTE,
            'docs_root'    => COCKPIT_DOCS_ROOT,
            'session.name' => md5(__DIR__),
            'sec-key'      => 'c3b40c4c-db44-s5h7-a814-b4931a15e5e1',
            'i18n'         => 'en',
            'database'     => [ "server" => "mongolite://".(__DIR__."/storage/data"), "options" => ["db" => "cockpitdb"] ]
        ], include(__DIR__.'/config.php'));

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
        $app->path('site'    , COCKPIT_DIR == COCKPIT_DOCS_ROOT ? COCKPIT_DIR : dirname(__DIR__));

        // nosql storage
        $app->service('db', function() use($config) {
            $client = new MongoHybrid\Client($config['database']['server'], $config['database']['options']);
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
            $mailer    = new \Mailer(isset($options["transport"]) ? $options['transport'] : 'mail', $options);
            return $mailer;
        });

        // set cache path
        $tmppath = $app->path('cache:tmp');
        $app("cache")->setCachePath($tmppath);
        $app->renderer->setCachePath($tmppath);

        // i18n
        $app("i18n")->locale = isset($config['i18n']) ? $config['i18n'] : 'en';

        // load modules
        $app->loadModules([
            __DIR__.'/modules/core',  # core
            __DIR__.'/modules/addons' # addons
        ]);
    }

    return $module ? $app->module($module) : $app;
}

$cockpit = cockpit();