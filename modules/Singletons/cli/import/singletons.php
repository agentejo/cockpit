<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!COCKPIT_CLI) return;

$src = $app->param('src', null);

if (!$src) {
    return;
}

$fs = $app->helper('fs');
$singletons = $fs->ls("{$src}/singletons");
$check = $app->param('check', false);

if (count($singletons)) {

    foreach ($singletons as $__file) {

        $name = $__file->getBasename('.json');

        if ($_singleton = $app->module('singletons')->singleton($name)) {

            $data = $fs->read($__file->getRealPath());

            if ($data = json_decode($data, true)) {

                CLI::writeln("Importing singletons/{$name}");

                if ($check) {
                    if (!$app->storage->getKey('singletons', $name)) {
                        $app->storage->setKey('singletons', $name, $data);
                    }
                } else {
                    $app->storage->setKey('singletons', $name, $data);
                }
            }
        }
    }
}
