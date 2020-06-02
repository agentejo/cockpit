<?php

if (!COCKPIT_CLI) return;

$name = $app->param('name', null);
$to = $app->param('to', null);

if (!$name) {
    return CLI::writeln("--name parameter is missing", false);
}

if (!$to) {
    return CLI::writeln("--to parameter is missing", false);
}

if (!$app->module('collections')->exists($name)) {
    return CLI::writeln("<{$name}> collection does not exist", false);
}

if ($app->module('collections')->exists($to)) {
    return CLI::writeln("<{$to}> collection does already exist", false);
}

if ($app->module('collections')->renameCollection($name, $to)) {
    return CLI::writeln("Collection <{$name}> renamed to <{$to}>", true);
} else {
    return CLI::writeln("Renaming collection failed", false);
}

