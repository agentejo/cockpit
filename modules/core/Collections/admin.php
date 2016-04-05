<?php

// ACL
$app("acl")->addResource("collections", ['manage.collections']);


$app->on('admin.init', function() {

    $this->helper('admin')->data['components']->append('collections:assets/field-collectionlink.tag');

    if (!$this->module('cockpit')->hasaccess('collections', ['manage.collections'])) {
        return;
    }

    // bind admin routes /collections/*
    $this->bindClass('Collections\\Controller\\Admin', 'collections');

    // add to modules menu
    $this('admin')->addMenuItem('modules', [
        'label' => 'Collections',
        'icon'  => 'database',
        'route' => '/collections',
        'active' => strpos($this['route'], '/collections') === 0
    ]);

    /**
     * listen to app search to filter collections
     */
    $this->on('cockpit.search', function($search, $list) {

        foreach ($this->module('collections')->collections() as $collection => $meta) {

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

        $cols        = $this->module('collections')->collections();
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

        $collections = $this->module("collections")->collections(true);

        $widgets[] = [
            "name"    => "collections",
            "content" => $this->view("collections:views/widgets/dashboard.php", compact('collections')),
            "area"    => 'aside-left'
        ];

    }, 100);
});
