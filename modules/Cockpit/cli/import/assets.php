<?php

if (!COCKPIT_CLI) return;

$src = $app->param('src', null);

if (!$src) {
    return;
}

$fs = $app->helper('fs');
$uploads = $app->path('#uploads:');
$check = $app->param('check', false);


if ($data = $fs->read("{$src}/cockpit/assets.json")) {

    if ($assets = json_decode($data, true)) {

        if (count($assets)) {

            CLI::writeln("Importing cockpit/assets (".count($assets).")");

            foreach ($assets as $asset) {

                $path    = trim($asset['path'], '/');
                $_src    = "{$src}/cockpit/assets/{$path}";
                $_target = "{$uploads}/{$path}";

                if (!file_exists($_src)) continue;

                if ($check) {

                    if (!$app->storage->count('cockpit/assets', ['_id' => $asset['_id']])) {
                        $app->storage->insert('cockpit/assets', $asset);

                        $fs->mkdir(dirname($_target));
                        $fs->copy($_src, $_target);
                    }

                } else {

                    $app->storage->insert('cockpit/assets', $asset);

                    $fs->mkdir(dirname($_target));
                    $fs->copy($_src, $_target);
                }
            }
        }
    }
}
