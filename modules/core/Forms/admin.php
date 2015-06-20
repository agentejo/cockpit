<?php

// ACL
$app("acl")->addResource("forms", ['manage.forms']);


$app->on('admin.init', function() {

    if (!$this->module('cockpit')->hasaccess('forms', ['manage.forms'])) {
        return;
    }

    // bind admin routes /forms/*
    $this->bindClass('forms\\Controller\\Admin', 'forms');

    // add to modules menu
    $this('admin')->addMenuItem('modules', [
        'label' => 'Forms',
        'icon'  => 'inbox',
        'route' => '/forms',
        'active' => strpos($this['route'], '/forms') === 0
    ]);

    /**
     * listen to app search to filter forms
     */
    $this->on('cockpit.search', function($search, $list) {

        foreach ($this->module('forms')->forms() as $collection => $meta) {

            if (stripos($collection, $search)!==false || stripos($meta['label'], $search)!==false) {

                $list[] = [
                    'icon'  => 'inbox',
                    'title' => $meta['label'] ? $meta['label'] : $meta['name'],
                    'url'   => $this->routeUrl('/forms/entries/'.$meta['name'])
                ];
            }
        }
    });


    // dashboard widgets
    $this->on("admin.dashboard.aside", function() {
        $forms = $this->module("forms")->forms(true);
        $this->renderView("forms:views/widgets/dashboard.php", compact('forms'));
    }, 100);


});
