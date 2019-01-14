<?php

if (!COCKPIT_CLI) return;

CLI::writeln('Flushing cockpit/accounts data');


$app->storage->dropCollection('cockpit/accounts');
