<?php


// ADMIN

if(COCKPIT_ADMIN && !COCKPIT_REST) {


    $app->on("admin.init", function() {

        $user = $this("session")->read("cockpit.app.auth");

        if($user["group"]!='admin') return;

        $this->bindClass("Updater\\Controller\\Updater", "updater");
        $this->bindClass("Updater\\Controller\\Api", "api/updater");

    });
}