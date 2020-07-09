<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Collections\Controller;


class Utils extends \Cockpit\AuthController {

    public function getUserCollections() {

        \session_write_close();

        $collections = $this->module('collections')->getCollectionsInGroup(null, true);

        return $collections;
    }

    public function getLinkedOverview() {

        \session_write_close();

        $collection = $this->param('collection');
        $id = $this->param('id');

        if (!$id) {
            return false;
        }

        $return = new \ArrayObject([]);

        $collections = $this->app->module('collections')->collections();

        foreach ($collections as $name => $meta) {

            $label = isset($meta['label']) && $meta['label'] ? $meta['label'] : $name;
            $entries = [];

            if ($this->app->storage->type == 'mongolite') {

                $entries = $this->app->storage->find("collections/{$meta['_id']}", [
                    'filter' => function ($doc) use ($id) {
                        return strpos(json_encode($doc), $id) !== false;
                    }
                ])->toArray();

            }

            if ($this->app->storage->type == 'mongodb') {

                $entries = $this->app->storage->find("collections/{$meta['_id']}", [
                    'filter' => [
                        '$where' => "function() { return JSON.stringify(this).indexOf('{$id}') > -1; }"
                    ]
                ])->toArray();
            }

            if (count($entries)) {
                
                if (!isset($return['collections'])) $return['collections'] = [];
                if (!isset($return['collections'][$label])) $return['collections'][$label] = [];

                foreach ($entries as $entry) {

                    if ($entry['_id'] == $id) continue;

                    $_label = "{$name}/{$entry['_id']}";

                    if (isset($entry['title'])) {
                        $_label = $entry['title'];
                    } elseif (isset($entry['name'])) {
                        $_label = $entry['name'];
                    }

                    $return['collections'][$label][] = [
                        'label' => $_label,
                        'link' => $this->app->routeUrl("/collections/entry/{$name}/{$entry['_id']}")
                    ];
                }

            }

            if (isset($return['collections'][$label]) && !count($return['collections'][$label])) {
                unset($return['collections'][$label]);
            }
        }

        if (isset($return['collections']) && !count($return['collections'])) unset($return['collections']);

        $this->app->trigger('collections.linkeditems', [$id, $return]);

        return $return;

    }
}