<?php


// ADMIN

if(COCKPIT_ADMIN && !COCKPIT_REST) {


    $app->on("admin.init", function() use($app){

        $user = $app("session")->read("cockpit.app.auth");

        if($user["group"]!='admin') return;

        $app->bindClass("Updater\\Controller\\Updater", "updater");
        $app->bindClass("Updater\\Controller\\Api", "api/updater");

    });
}