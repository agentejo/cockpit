<?php

define('COCKPIT_ADMIN', 1);

// set default timezone
date_default_timezone_set('UTC');

// handle php webserver
if (PHP_SAPI == 'cli-server' && is_file(__DIR__.parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// bootstrap cockpit
require(__DIR__.'/bootstrap.php');

# admin route
if (COCKPIT_ADMIN && !defined('COCKPIT_ADMIN_ROUTE')) {
    $route = preg_replace('#'.preg_quote(COCKPIT_BASE_URL, '#').'#', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), 1);
    define('COCKPIT_ADMIN_ROUTE', $route == '' ? '/' : $route);
}

// run backend
$cockpit->set('route', COCKPIT_ADMIN_ROUTE)->trigger("admin.init")->run();
