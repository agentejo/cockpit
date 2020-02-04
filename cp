#!/usr/bin/env php
<?php

if (PHP_SAPI !== 'cli') {
    exit('Script needs to be run from Command Line Interface (cli)');
}

define('COCKPIT_CLI', true);

// set default timezone
date_default_timezone_set('UTC');

include_once(__DIR__.'/bootstrap.php');

$_REQUEST = CLI::opts(); // make option available via $app->param()
$app = cockpit();

$request = new \Lime\Request([
    'request'    => $_REQUEST,
    'server'     => $_SERVER,
    'site_url'   => $app['site_url'],
    'base_url'   => $app['base_url'],
    'base_route' => $app['base_route']
]);

$app->request = $request;

register_shutdown_function(function() use($app){
    $app->trigger('shutdown');
});

set_exception_handler(function($exception) use($app) {

    $error = [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
    ];

    $app->trigger('error', [$error, $exception]);

    if (function_exists('cockpit_error_handler')) {
        cockpit_error_handler($error);
    }

    CLI::writeln('COCKPIT CLI ERROR:', false);
    CLI::writeln('-> in '.$error['file'].':'.$error['line']."\n");
    CLI::writeln($error['message']."\n");
});

if (isset($argv[1])) {

    $cmd = str_replace('../', '', $argv[1]);
    $script = $app->path("#config:cli/{$cmd}.php");

    if (!$script) {
        $script = $app->path("#cli:{$cmd}.php");
    }

    switch ($cmd) {

        case 'test':
            CLI::writeln('Yepp!', true);
            break;

        default:

            if ($script) {
                include($script);
            } else {
                CLI::writeln("Error: Command \"{$cmd}\" not found!", false);
            }
    }
}
