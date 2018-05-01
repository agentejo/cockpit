<?php

if (!COCKPIT_CLI) return;

$target = $app->param('target', null);

if (!$target) {
    return;
}

$accounts = $app->storage->find('cockpit/accounts')->toArray();


if (count($accounts)) {

    CLI::writeln("Exporting cockpit/accounts (".count($accounts).")");

    $app->helper('fs')->write("{$target}/cockpit/accounts.json", json_encode($accounts, JSON_PRETTY_PRINT));
}
