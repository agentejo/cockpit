<?php

// ACL
$app("acl")->addResource("regions", ['create', 'delete']);


$this->module("regions")->extend([

    'getRegionsInGroup' => function($group = null) {

        if (!$group) {
            $group = $this->app->module('cockpit')->getGroup();
        }

        $_regions = $this->regions();
        $regions = [];

        if ($this->app->module('cockpit')->isSuperAdmin()) {
            return $_regions;
        }

        foreach ($_regions as $region => $meta) {

            if (isset($meta['acl'][$group]['form']) && $meta['acl'][$group]['form']) {
                $regions[$region] = $meta;
            }
        }

        return $regions;
    },

    'hasaccess' => function($region, $action, $group = null) {

        $region = $this->region($region);

        if (!$region) {
            return false;
        }

        if ($this->app->module('cockpit')->isSuperAdmin()) {
            return true;
        }

        if (!$group) {
            $group = $this->app->module('cockpit')->getGroup();
        }

        if (isset($region['acl'][$group][$action])) {
            return $region['acl'][$group][$action];
        }

        return false;
    }
]);


$app->on('admin.init', function() {


    if (!$this->module('cockpit')->getGroupRights('regions') && !$this->module('regions')->getRegionsInGroup()) {

        $this->bind('/regions/*', function() {
            return $this('admin')->denyRequest();
        });

        return;
    }

    // bind admin routes /regions/*
    $this->bindClass('Regions\\Controller\\Admin', 'regions');

    // add to modules menu
    $this('admin')->addMenuItem('modules', [
        'label' => 'Regions',
        'icon'  => 'regions:icon.svg',
        'route' => '/regions',
        'active' => strpos($this['route'], '/regions') === 0
    ]);

    /**
     * listen to app search to filter regions
     */
    $this->on('cockpit.search', function($search, $list) {

        foreach ($this->module('regions')->getRegionsInGroup() as $region => $meta) {

            if (stripos($region, $search)!==false || stripos($meta['label'], $search)!==false) {

                $list[] = [
                    'icon'  => 'th',
                    'title' => $meta['label'] ? $meta['label'] : $meta['name'],
                    'url'   => $this->routeUrl('/regions/region/'.$meta['name'])
                ];
            }
        }
    });

    // dashboard widgets
    $this->on("admin.dashboard.widgets", function($widgets) {

        $regions = $this->module("regions")->getRegionsInGroup();

        $widgets[] = [
            "name"    => "regions",
            "content" => $this->view("regions:views/widgets/dashboard.php", compact('regions')),
            "area"    => 'aside-right'
        ];

    }, 100);
});
