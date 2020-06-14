<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app->on('admin.init', function() {

    $this->helper('admin')->addAssets('collections:assets/field-collectionlink.tag');
    $this->helper('admin')->addAssets('collections:assets/field-collectionlinkselect.tag');
    $this->helper('admin')->addAssets('collections:assets/link-collectionitem.js');

    if (!$this->module('cockpit')->getGroupRights('collections') && !$this->module('collections')->getCollectionsInGroup()) {

        $this->bind('/collections/*', function() {
            return $this('admin')->denyRequest();
        });

        return;
    }

    // bind admin routes /collections/*
    $this->bindClass('Collections\\Controller\\Trash', 'collections/trash');
    $this->bindClass('Collections\\Controller\\Import', 'collections/import');
    $this->bindClass('Collections\\Controller\\Utils', 'collections/utils');
    $this->bindClass('Collections\\Controller\\Admin', 'collections');

    $active = strpos($this['route'], '/collections') === 0;

    // add to modules menu
    $this->helper('admin')->addMenuItem('modules', [
        'label' => 'Collections',
        'icon'  => 'collections:icon.svg',
        'route' => '/collections',
        'active' => $active
    ]);

    if ($active) {
        $this->helper('admin')->favicon = 'collections:icon.svg';
    } 

    /**
     * listen to app search to filter collections
     */
    $this->on('cockpit.search', function($search, $list) {

        foreach ($this->module('collections')->getCollectionsInGroup() as $collection => $meta) {

            if (stripos($collection, $search)!==false || stripos($meta['label'], $search)!==false) {

                $list[] = [
                    'icon'  => 'database',
                    'title' => $meta['label'] ? $meta['label'] : $meta['name'],
                    'url'   => $this->routeUrl('/collections/entries/'.$meta['name'])
                ];
            }
        }
    });

    // dashboard widgets
    $this->on("admin.dashboard.widgets", function($widgets) {

        $collections = $this->module("collections")->getCollectionsInGroup(null, false);

        $widgets[] = [
            "name"    => "collections",
            "content" => $this->view("collections:views/widgets/dashboard.php", compact('collections')),
            "area"    => 'aside-left'
        ];

    }, 100);

    // register events for autocomplete
    $this->on('cockpit.webhook.events', function($triggers) {

        foreach([
            'collections.createcollection',
            'collections.find.after',
            'collections.find.after.{$name}',
            'collections.find.before',
            'collections.find.before.{$name}',
            'collections.remove.after',
            'collections.remove.after.{$name}',
            'collections.remove.before',
            'collections.remove.before.{$name}',
            'collections.removecollection',
            'collections.removecollection.{$name}',
            'collections.save.after',
            'collections.save.after.{$name}',
            'collections.save.before',
            'collections.save.before.{$name}',
            'collections.updatecollection',
            'collections.updatecollection.{$name}'
        ] as &$evt) { $triggers[] = $evt; }
    });
});
