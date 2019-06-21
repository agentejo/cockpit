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

$collection = $app->param('collection', null);
$field = $app->param('field', null);
$newfield = $app->param('newfield', null);

if (!$collection) {
    CLI::writeln('Collection parameter missing', false);
    return;
}

if (!$field) {
    CLI::writeln('Field parameter missing', false);
    return;
}

if (!$newfield) {
    CLI::writeln('New Field <newfield> parameter missing', false);
    return;
}

$meta = $app->module('collections')->collection($collection);

if (!$meta) {
    CLI::writeln("Collection <{$collection}> not found!", false);
    return;
}

$id = $meta['_id'];

$app->storage->renameField("collections/{$id}", $field, $newfield);

CLI::writeln("Field <{$field}> renamed to <{$newfield}>!", true);