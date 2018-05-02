<?php

if (!COCKPIT_CLI) return;

$target = $app->param('target', null);

if (!$target) {
    return;
}

foreach ($app->module('singletons')->singletons() as $name => $singleton) {

    $data = $app->storage->getKey('singletons', $name);

    if (count($data)) {

        CLI::writeln("Exporting singletons/{$name}");

        $app->helper('fs')->write("{$target}/singletons/{$name}.json", json_encode($data, JSON_PRETTY_PRINT));
    }
}
