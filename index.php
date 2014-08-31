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
        case 404:

            if ($this->response->status == 500 && $this->param('debug', false)) {
                return;
            }

            if ($this->req_is('ajax')) {
                $this->response->body = '{"error": "'.$this->response->status.'"}';
            } else {
                $this->response->body = $this->view("cockpit:views/errors/{$this->response->status}.php");
            }
            break;
    }
});

// run backend
$cockpit->trigger("admin.init")->run(COCKPIT_ADMIN_ROUTE);