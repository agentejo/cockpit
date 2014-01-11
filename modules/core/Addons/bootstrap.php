<?php

// ADMIN

if(COCKPIT_ADMIN) {

    $app->bindClass("Addons\\Controller\\Addons", "settings/addons");
}