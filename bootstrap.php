<?php

/**
 * Cockpit start time
 */
define('COCKPIT_START_TIME', microtime(true));

if (!defined('COCKPIT_CLI')) define('COCKPIT_CLI', PHP_SAPI == 'cli');

/*
 * Autoload from lib folder (PSR-0)
 */

spl_autoload_register(function($class){
    $class_path = __DIR__.'/lib/'.str_replace('\\', '/', $class).'.php';
    if(file_exists($class_path)) include_once($class_path);
});

// check for custom defines
if (file_exists(__DIR__.'/defines.php')) {
    include(__DIR__.'/defines.php');
}

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
$COCKPIT_BASE_ROUTE  = $COCKPIT_BASE_URL;

/*
 * SYSTEM DEFINES
 */
if (!defined('COCKPIT_ADMIN'))                  define('COCKPIT_ADMIN'          , 0);
if (!defined('COCKPIT_API_REQUEST'))            define('COCKPIT_API_REQUEST'    , COCKPIT_ADMIN && strpos($_SERVER['REQUEST_URI'], $COCKPIT_BASE_URL.'/api/')!==false ? 1:0);
if (!defined('COCKPIT_DIR'))                    define('COCKPIT_DIR'            , $COCKPIT_DIR);
if (!defined('COCKPIT_SITE_DIR'))               define('COCKPIT_SITE_DIR'       , $COCKPIT_DIR == $COCKPIT_DOCS_ROOT ? $COCKPIT_DIR : dirname($COCKPIT_DIR));
if (!defined('COCKPIT_CONFIG_DIR'))             define('COCKPIT_CONFIG_DIR'     , COCKPIT_DIR.'/config');
if (!defined('COCKPIT_DOCS_ROOT'))              define('COCKPIT_DOCS_ROOT'      , $COCKPIT_DOCS_ROOT);
if (!defined('COCKPIT_BASE_URL'))               define('COCKPIT_BASE_URL'       , $COCKPIT_BASE_URL);
if (!defined('COCKPIT_BASE_ROUTE'))             define('COCKPIT_BASE_ROUTE'     , $COCKPIT_BASE_ROUTE);
if (!defined('COCKPIT_STORAGE_FOLDER'))         define('COCKPIT_STORAGE_FOLDER' , COCKPIT_DIR.'/storage');
if (!defined('COCKPIT_PUBLIC_STORAGE_FOLDER'))  define('COCKPIT_PUBLIC_STORAGE_FOLDER' , COCKPIT_DIR.'/storage');

if (!defined('COCKPIT_CONFIG_PATH')) {
    $_configpath = COCKPIT_CONFIG_DIR.'/config.'.(file_exists(COCKPIT_CONFIG_DIR.'/config.php') ? 'php':'yaml');
    define('COCKPIT_CONFIG_PATH', $_configpath);
}


function cockpit($module = null) {

    static $app;

    if (!$app) {

        $customconfig = [];

        // load custom config
        if (file_exists(COCKPIT_CONFIG_PATH)) {
            $customconfig = preg_match('/\.yaml$/', COCKPIT_CONFIG_PATH) ? Spyc::YAMLLoad(COCKPIT_CONFIG_PATH) : include(COCKPIT_CONFIG_PATH);
        }

        // load config
        $config = array_replace_recursive([

            'debug'        => preg_match('/(localhost|::1|\.dev)$/', @$_SERVER['SERVER_NAME']),
            'app.name'     => 'Cockpit',
            'base_url'     => COCKPIT_BASE_URL,
            'base_route'   => COCKPIT_BASE_ROUTE,
            'docs_root'    => COCKPIT_DOCS_ROOT,
            'session.name' => md5(__DIR__),
            'sec-key'      => 'c3b40c4c-db44-s5h7-a814-b4931a15e5e1',
            'i18n'         => 'en',
            'database'     => [ "server" => "mongolite://".(COCKPIT_STORAGE_FOLDER."/data"), "options" => ["db" => "cockpitdb"] ],
            'memory'       => [ "server" => "redislite://".(COCKPIT_STORAGE_FOLDER."/data/cockpit.memory.sqlite"), "options" => [] ],

            'paths'         => [
                '#root'     => COCKPIT_DIR,
                '#storage'  => COCKPIT_STORAGE_FOLDER,
                '#pstorage' => COCKPIT_PUBLIC_STORAGE_FOLDER,
                '#data'     => COCKPIT_STORAGE_FOLDER.'/data',
                '#cache'    => COCKPIT_STORAGE_FOLDER.'/cache',
                '#tmp'      => COCKPIT_STORAGE_FOLDER.'/tmp',
                '#thumbs'   => COCKPIT_PUBLIC_STORAGE_FOLDER.'/thumbs',
                '#uploads'  => COCKPIT_PUBLIC_STORAGE_FOLDER.'/uploads',
                '#modules'  => COCKPIT_DIR.'/modules',
                '#addons'   => COCKPIT_DIR.'/addons',
                '#config'   => COCKPIT_CONFIG_DIR,
                'assets'    => COCKPIT_DIR.'/assets',
                'site'      => COCKPIT_SITE_DIR
            ]

        ], is_array($customconfig) ? $customconfig : []);

        $app = new LimeExtra\App($config);

        $app["config"] = $config;

        // register paths
        foreach ($config['paths'] as $key => $path) {
            $app->path($key, $path);
        }

        // nosql storage
        $app->service('storage', function() use($config) {
            $client = new MongoHybrid\Client($config['database']['server'], $config['database']['options']);
            return $client;
        });

        // key-value storage
        $app->service('memory', function() use($config) {
            $client = new SimpleStorage\Client($config['memory']['server'], $config['memory']['options']);
            return $client;
        });

        // mailer service
        $app->service("mailer", function() use($app, $config){
            $options   = isset($config['mailer']) ? $config['mailer']:[];
            $mailer    = new \Mailer(isset($options["transport"]) ? $options['transport'] : 'mail', $options);
            return $mailer;
        });

        // set cache path
        $tmppath = $app->path('#tmp:');

        $app("cache")->setCachePath($tmppath);
        $app->renderer->setCachePath($tmppath);

        // i18n
        $app("i18n")->locale = isset($config['i18n']) ? $config['i18n'] : 'en';

        // load modules
        $app->loadModules(array_merge([
            COCKPIT_DIR.'/modules',  # core
            COCKPIT_DIR.'/addons' # addons
        ], isset($config['loadmodules']) ? (array) $config['loadmodules'] : []));

        // load config global bootstrap
        if ($custombootfile = $app->path('#config:bootstrap.php')) {
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
