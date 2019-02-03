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
$collections = $fs->ls("{$src}/collections");
$check = $app->param('check', false);

if (count($collections)) {

    foreach ($collections as $__file) {

        $name = $__file->getBasename('.json');

        if ($_collection = $app->module('collections')->collection($name)) {

            $cid  = $_collection['_id'];
            $data = $fs->read($__file->getRealPath());

            if ($items = json_decode($data, true)) {

                CLI::writeln("Importing collections/{$name} (".count($items).")");

                foreach ($items as &$item) {

                    if ($check) {
                        if (!$app->storage->count("collections/{$cid}", ['_id' => $item['_id']])) {
                            $app->storage->insert("collections/{$cid}", $item);
                        }
                    } else {
                        $app->storage->insert("collections/{$cid}", $item);
                    }
                }
            }
        }
    }
}
