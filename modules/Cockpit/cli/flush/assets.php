<?php

if (!COCKPIT_CLI) return;

CLI::writeln('Flushing cockpit/assets data');


$app->storage->dropCollection('cockpit/assets');
$app->storage->dropCollection('cockpit/assets_folders');
