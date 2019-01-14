<?php

if (!COCKPIT_CLI) return;

CLI::writeln('Flushing collections data');


foreach ($app->module('collections')->collections() as $name => $collection) {

    $cid = $collection['_id'];
    $app->storage->dropCollection("collections/{$cid}");
}
