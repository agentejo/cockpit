<?php

if (!COCKPIT_CLI) return;

$source = $app->param('src', null);

if (!$source) {
    return CLI::writeln("--src parameter is missing", false);
}


$options = new ArrayObject([
    'src' => $source
]);

$fs = $app->helper('fs');

foreach ($app->paths('#cli') as $__dir) {

    foreach ($fs->ls($__dir.'import') as $__file) {
        include($__file->getRealPath());
    }
}

CLI::writeln("Done importing data.", true);

$app->trigger('cockpit.import', [$options]);
