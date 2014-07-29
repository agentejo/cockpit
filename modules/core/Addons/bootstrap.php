<?php

// ADMIN

if (COCKPIT_ADMIN && !COCKPIT_REST) {

    $app->bindClass("Addons\\Controller\\Addons", "settings/addons");
}