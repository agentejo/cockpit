<?php

if (!COCKPIT_CLI) return;

$target = $app->param('target', null);

if (!$target) {
    return;
}

$assets = $app->storage->find('cockpit/assets')->toArray();

if (count($assets)) {

    CLI::writeln("Exporting cockpit/assets (".count($assets).")");

    $app->helper('fs')->write("{$target}/cockpit/assets.json", json_encode($assets, JSON_PRETTY_PRINT));
}
