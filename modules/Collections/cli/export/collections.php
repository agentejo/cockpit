<?php

if (!COCKPIT_CLI) return;

$target = $app->param('target', null);

if (!$target) {
    return;
}

foreach ($app->module('collections')->collections() as $name => $collection) {

    $cid = $collection['_id'];
    $items = $app->storage->find("collections/{$cid}")->toArray();

    if (count($items)) {

        CLI::writeln("Exporting collections/{$name} (".count($items).")");

        $app->helper('fs')->write("{$target}/collections/{$name}.json", json_encode($items, JSON_PRETTY_PRINT));
    }
}
