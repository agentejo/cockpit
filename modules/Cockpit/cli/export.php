<?php

if (!COCKPIT_CLI) return;

$options = new ArrayObject([
    'target' => $app->param('target', null)
]);

$fs = $app->helper('fs');

foreach ($app->paths('#cli') as $__dir) {

    foreach ($fs->ls($__dir.'export') as $__file) {
        include($__file->getRealPath());
    }
}

CLI::writeln("Done exporting data.", true);

$app->trigger('cockpit.export', [$options]);
