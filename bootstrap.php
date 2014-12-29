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
$COCKPIT_BASE_ROUTE  = isset($_SERVER['COCKPIT_URL_REWRITE']) && $_SERVER['COCKPIT_URL_REWRITE'] == 'On' ? $COCKPIT_BASE_URL : "{$COCKPIT_BASE_URL}/index.php";

/*
 * SYSTEM DEFINES
 */
if (!defined('COCKPIT_ADMIN'))       define('COCKPIT_ADMIN'      , 0);
if (!defined('COCKPIT_REST'))        define('COCKPIT_REST'       , COCKPIT_ADMIN && strpos($_SERVER['REQUEST_URI'], '/rest/api')!==false ? 1:0);
if (!defined('COCKPIT_DIR'))         define('COCKPIT_DIR'        , $COCKPIT_DIR);
if (!defined('COCKPIT_DOCS_ROOT'))   define('COCKPIT_DOCS_ROOT'  , $COCKPIT_DOCS_ROOT);
if (!defined('COCKPIT_BASE_URL'))    define('COCKPIT_BASE_URL'   , $COCKPIT_BASE_URL);
if (!defined('COCKPIT_BASE_ROUTE'))  define('COCKPIT_BASE_ROUTE' , $COCKPIT_BASE_ROUTE);
if (!defined('COCKPIT_CONFIG_PATH')) define('COCKPIT_CONFIG_PATH', COCKPIT_DIR . '/custom/config.php');

# admin route
if (COCKPIT_ADMIN && !defined('COCKPIT_ADMIN_ROUTE')) {

    $route = str_replace([COCKPIT_BASE_URL.'/index.php', COCKPIT_BASE_URL], '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

    define('COCKPIT_ADMIN_ROUTE', $route == '' ? '/' : $route);
}

function cockpit($module = null) {

    static $app;

    if (!$app) {

        // load config
        $config = array_replace_recursive([

            'debug'        => false,
            'app.name'     => 'Cockpit',
            'base_url'     => COCKPIT_BASE_URL,
            'base_route'   => COCKPIT_BASE_ROUTE,
            'docs_root'    => COCKPIT_DOCS_ROOT,
            'session.name' => md5(__DIR__),
            'sec-key'      => 'c3b40c4c-db44-s5h7-a814-b4931a15e5e1',
            'i18n'         => 'en',
            'database'     => [ "server" => "mongolite://".(COCKPIT_DIR."/storage/data"), "options" => ["db" => "cockpitdb"] ],

            'paths'        => [
                '#root'   => COCKPIT_DIR,
                'storage' => COCKPIT_DIR.'/storage',
                '#backups'=> COCKPIT_DIR.'/storage/backups',
                'data'    => COCKPIT_DIR.'/storage/data',
                'cache'   => COCKPIT_DIR.'/storage/cache',
                'tmp'     => COCKPIT_DIR.'/storage/cache/tmp',
                'modules' => COCKPIT_DIR.'/modules',
                'assets'  => COCKPIT_DIR.'/assets',
                'custom'  => COCKPIT_DIR.'/custom',
                'site'    => COCKPIT_DIR == COCKPIT_DOCS_ROOT ? COCKPIT_DIR : dirname(COCKPIT_DIR)
            ]

        ], file_exists(COCKPIT_CONFIG_PATH) ? include(COCKPIT_CONFIG_PATH) : []);

        $app = new LimeExtra\App($config);

        $app["app.config"] = $config;

        // register paths
        foreach ($config['paths'] as $key => $path) {
            $app->path($key, $path);
        }

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
            COCKPIT_DIR.'/modules/core',  # core
            COCKPIT_DIR.'/modules/addons' # addons
        ]);

        // load custom global bootstrap
        if ($custombootfile = $app->path('custom:bootstrap.php')) {
            include($custombootfile);
        }

        $app->trigger('cockpit.bootstrap');
    }

    // shorthand modules method call e.g. cockpit('regions:render', 'test');
    if (func_num_args() > 1) {

        $arguments = func_get_args();

        list($module, $method) = explode(':', $arguments[0]);
        array_splice($arguments, 0, 1);
        return call_user_func_array([$app->module($module), $method], $arguments);
    }

    return $module ? $app->module($module) : $app;
}

$cockpit = cockpit();