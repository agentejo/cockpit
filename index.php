<?php

define('COCKPIT_ADMIN', 1);

date_default_timezone_set('UTC');

// $_SERVER['PATH_INFO'] fix for nginx >:-/
if (!isset($_SERVER['PATH_INFO']) && strpos($_SERVER['REQUEST_URI'], $_SERVER['PHP_SELF'])===0) {

    $_URI  = preg_replace('/\?(.*)/', '', $_SERVER['REQUEST_URI']);
    $_SELF = $_SERVER['PHP_SELF'];
    $_PATH = substr($_URI, strlen($_SELF));

    if ($_PATH && $_PATH[0] == '/') $_SERVER['PATH_INFO'] = $_PATH;
}

require(__DIR__.'/bootstrap.php');

$cockpit->on("after", function() {

    switch ($this->response->status) {
        case 500:
        case 404:
            if ($this->req_is('ajax')) {
                $this->response->body = '{"error": "'.$this->response->status.'"}';
            } else {
                $this->response->body = $this->view("cockpit:views/errors/{$this->response->status}.php");
            }
            break;
    }
});

$cockpit->trigger("admin.init")->run();