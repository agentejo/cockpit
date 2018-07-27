<?php

$app->on('admin.init', function() {


    if (!$this->module('cockpit')->getGroupRights('singletons') && !$this->module('singletons')->getSingletonInGroup()) {

        $this->bind('/singletons/*', function() {
            return $this('admin')->denyRequest();
        });

        return;
    }

    // bind admin routes /singleton/*
    $this->bindClass('Singletons\\Controller\\Admin', 'singletons');

    // add to modules menu
    $this('admin')->addMenuItem('modules', [
        'label' => 'Singletons',
        'icon'  => 'singletons:icon.svg',
        'route' => '/singletons',
        'active' => strpos($this['route'], '/singletons') === 0
    ]);

    /**
     * listen to app search to filter singleton
     */
    $this->on('cockpit.search', function($search, $list) {

        foreach ($this->module('singletons')->getSingletonsInGroup() as $singleton => $meta) {

            if (stripos($singleton, $search)!==false || stripos($meta['label'], $search)!==false) {

                $list[] = [
                    'icon'  => 'th',
                    'title' => $meta['label'] ? $meta['label'] : $meta['name'],
                    'url'   => $this->routeUrl('/singletons/singleton/'.$meta['name'])
                ];
            }
        }
    });

    $this->on('cockpit.menu.aside', function() {

        $singletons = [];

        foreach ($this->module('singletons')->getSingletonsInGroup() as $singleton) {

            if (isset($singleton['in_menu']) && $singleton['in_menu']) {
                $singletons[] = $singleton;
            }
        }

        if (count($singletons)) {
            $this->renderView("singletons:views/partials/menu.php", compact('singletons'));
        }
    });

    // dashboard widgets
    $this->on("admin.dashboard.widgets", function($widgets) {

        $singletons = $this->module("singletons")->getSingletonsInGroup();

        $widgets[] = [
            "name"    => "singleton",
            "content" => $this->view("singletons:views/widgets/dashboard.php", compact('singletons')),
            "area"    => 'aside-right'
        ];

    }, 100);
});
