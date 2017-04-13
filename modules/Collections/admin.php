<?php

$app->on('admin.init', function() {

    $this->helper('admin')->addAssets('collections:assets/field-collectionlink.tag');

    if (!$this->module('cockpit')->getGroupRights('collections') && !$this->module('collections')->getCollectionsInGroup()) {

        $this->bind('/collections/*', function() {
            return $this('admin')->denyRequest();
        });

        return;
    }

    // bind admin routes /collections/*
    $this->bindClass('Collections\\Controller\\Import', 'collections/import');
    $this->bindClass('Collections\\Controller\\Admin', 'collections');

    // add to modules menu
    $this('admin')->addMenuItem('modules', [
        'label' => 'Collections',
        'icon'  => 'collections:icon.svg',
        'route' => '/collections',
        'active' => strpos($this['route'], '/collections') === 0
    ]);

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

    $this->on('cockpit.menu.aside', function() {

        $cols        = $this->module('collections')->getCollectionsInGroup();
        $collections = [];

        foreach($cols as $collection) {
            if ($collection['in_menu']) $collections[] = $collection;
        }

        if (count($collections)) {
            $this->renderView("collections:views/partials/menu.php", compact('collections'));
        }
    });

    // dashboard widgets
    $this->on("admin.dashboard.widgets", function($widgets) {

        $collections = $this->module("collections")->getCollectionsInGroup(null, true);

        $widgets[] = [
            "name"    => "collections",
            "content" => $this->view("collections:views/widgets/dashboard.php", compact('collections')),
            "area"    => 'aside-left'
        ];

    }, 100);
});
