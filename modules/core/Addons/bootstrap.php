<?php

// ADMIN

if(COCKPIT_ADMIN) {

    $app->bindClass("Addons\\Controller\\addons", "settings/addons");
}