<?php

define('COCKPIT_ADMIN', 1);

date_default_timezone_set('UTC');

require(__DIR__.'/bootstrap.php');

$cockpit = cockpit();

$cockpit->on("after", function() use($cockpit) {

    switch ($cockpit->response->status) {
        case 500:
        case 404:
            $cockpit->response->body = $cockpit->view("cockpit:views/errors/{$cockpit->response->status}.php");
            break;
    }
});

$cockpit->trigger("admin.init")->run();