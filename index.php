<?php

define('COCKPIT_ADMIN', 1);

// set default url rewrite setting
if (!isset($_SERVER['COCKPIT_URL_REWRITE'])) {
    $_SERVER['COCKPIT_URL_REWRITE'] = 'Off';
}

// set default timezone
date_default_timezone_set('UTC');

// handle php webserver
if (PHP_SAPI == 'cli-server' && is_file(__DIR__.parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// bootstrap cockpit
require(__DIR__.'/bootstrap.php');

// handle error pages
$cockpit->on("after", function() {

    switch ($this->response->status) {
        case 500:

            if ($this['debug']) {

                if ($this->req_is('ajax')) {
                    $this->response->body = json_encode(['error' => json_decode($this->response->body, true)]);
                } else {
                    $this->response->body = $this->render("cockpit:views/errors/500-debug.php", ['error' => json_decode($this->response->body, true)]);
                }

            } else {

                if ($this->req_is('ajax')) {
                    $this->response->body = '{"error": "500", "message": "system error"}';
                } else {
                    $this->response->body = $this->view("cockpit:views/errors/500.php");
                }
            }

            break;

        case 404:

            if ($this->req_is('ajax')) {
                $this->response->body = '{"error": "404", "message":"File not found"}';
            } else {
                $this->response->body = $this->view("cockpit:views/errors/404.php");
            }
            break;
    }
});

// run backend
$cockpit->set('route', COCKPIT_ADMIN_ROUTE)->trigger("admin.init")->run();
