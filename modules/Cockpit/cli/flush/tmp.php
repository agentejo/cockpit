<?php

if (!COCKPIT_CLI) return;

CLI::writeln('Flushing cockpit/tmp data');

$app->module('cockpit')->clearCache();
