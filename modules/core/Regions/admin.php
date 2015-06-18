<?php

// ACL
$app("acl")->addResource("regions", ['manage.regions']);


$app->on('admin.init', function() {


    if (!$this->module('cockpit')->hasaccess('regions', ['manage.regions'])) {
        return;
    }

    // bind admin routes /regions/*
    $this->bindClass('Regions\\Controller\\Admin', 'regions');

    // add to modules menu
    $this['app.menu.modules']->append([
        'label' => 'Regions',
        'route' => '/regions'
    ]);

    /**
     * listen to app search to filter regions
     */
    $this->on('cockpit.search', function($search, $list) {

        foreach ($this->module('regions')->regions() as $region => $meta) {

            if (stripos($region, $search)!==false || stripos($meta['label'], $search)!==false) {

                $list[] = [
                    'icon'  => 'database',
                    'title' => $meta['label'] ? $meta['label'] : $meta['name'],
                    'url'   => $this->routeUrl('/regions/region/'.$meta['name'])
                ];
            }
        }
    });


    // dashboard widgets
    $this->on("admin.dashboard.aside", function() {
        $regions = $this->module("regions")->regions(true);
        $this->renderView("regions:views/widgets/dashboard.php", compact('regions'));
    }, 100);


});