<?php

if (!COCKPIT_CLI) return;

$target = $app->param('target', null);

if (!$target) {
    return;
}

$fs = $app->helper('fs');
$assets = $app->storage->find('cockpit/assets')->toArray();

if (count($assets)) {

    CLI::writeln("Exporting cockpit/assets (".count($assets).")");

    $fs->write("{$target}/cockpit/assets.json", json_encode($assets, JSON_PRETTY_PRINT));

    // move assets files
    foreach ($assets as &$asset) {

        $path = trim($asset['path'], '/');
        $_target = "{$target}/cockpit/assets/{$path}";

        if ($_path = $app->path("#uploads:{$path}")) {
            $fs->mkdir(dirname($_target));
            $fs->copy($_path, $_target);
        }
    }
}
