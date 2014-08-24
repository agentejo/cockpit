<?php

define('COCKPIT_ADMIN', 1);

date_default_timezone_set('UTC');


if (PHP_SAPI == 'cli-server' && is_file(__DIR__.parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}


if (!isset($_SERVER['COCKPIT_URL_REWRITE'])) {
    $_SERVER['COCKPIT_URL_REWRITE'] = 'Off';
}

require(__DIR__.'/bootstrap.php');

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


$route = str_replace([COCKPIT_BASE_URL.'/index.php', COCKPIT_BASE_URL], '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

$cockpit->trigger("admin.init")->run($route);