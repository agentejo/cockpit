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

$target = $app->param('target', null);

if (!$target) {
    return CLI::writeln("--target parameter is missing", false);
}

$options = new ArrayObject([
    'target' => $target
]);

$fs = $app->helper('fs');

foreach ($app->paths('#cli') as $__dir) {

    foreach ($fs->ls($__dir.'export') as $__file) {
        include($__file->getRealPath());
    }
}

CLI::writeln("Done exporting data.", true);

$app->trigger('cockpit.export', [$options]);
