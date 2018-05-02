<?php

if (!COCKPIT_CLI) return;


$src = $app->param('src', null);

if (!$src) {
    return;
}

$check = $app->param('check', false);

if ($data = $app->helper('fs')->read("{$src}/cockpit/accounts.json")) {

    if ($accounts = json_decode($data, true)) {

        if (count($accounts)) {

            CLI::writeln("Importing cockpit/accounts (".count($accounts).")");

            foreach ($accounts as $account) {
                if ($check) {
                    if (!$app->storage->count('cockpit/accounts', ['_id' => $account['_id']])) {
                        $app->storage->insert('cockpit/accounts', $account);
                    }
                } else {
                    $app->storage->insert('cockpit/accounts', $account);
                }
            }
        }
    }
}
