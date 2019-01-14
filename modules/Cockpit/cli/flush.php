<?php

if (!COCKPIT_CLI) return;

$fs = $app->helper('fs');

foreach ($app->paths('#cli') as $__dir) {

    foreach ($fs->ls($__dir.'flush') as $__file) {
        include($__file->getRealPath());
    }
}

CLI::writeln("Done flushing data.", true);

$app->trigger('cockpit.flush', []);
