<?php

define('COCKPIT_ADMIN', 1);

date_default_timezone_set('UTC');

require(__DIR__.'/bootstrap.php');

$app = c();

$app->on("after", function() use($app) {

    switch ($app->response->status) {
        case 500:
        case 404:
            if(!$app->req_is('ajax')) {
                $app->response->body = $app->view("cockpit:views/errors/{$app->response->status}.php");
            }
            break;
    }
});

$app->trigger("admin.init")->run();