<?php

$app->on('admin.init', function() {

    if (!$this->module('cockpit')->getGroupRights('forms')) {

        $this->bind('/forms/*', function() {
            return $this('admin')->denyRequest();
        });

        return;
    }

    // bind admin routes /forms/*
    $this->bindClass('Forms\\Controller\\Admin', 'forms');

    // add to modules menu
    $this('admin')->addMenuItem('modules', [
        'label' => 'Forms',
        'icon'  => 'forms:icon.svg',
        'route' => '/forms',
        'active' => strpos($this['route'], '/forms') === 0
    ]);

    $this->on('cockpit.menu.aside', function() {

        $frms  = $this->module('forms')->forms();
        $forms = [];

        foreach($frms as $form) {
            if ($form['in_menu']) $forms[] = $form;
        }

        if (count($forms)) {
            $this->renderView("forms:views/partials/menu.php", compact('forms'));
        }
    });

    /**
     * listen to app search to filter forms
     */
    $this->on('cockpit.search', function($search, $list) {

        foreach ($this->module('forms')->forms() as $form => $meta) {

            if (stripos($form, $search)!==false || stripos($meta['label'], $search)!==false) {

                $list[] = [
                    'icon'  => 'inbox',
                    'title' => $meta['label'] ? $meta['label'] : $meta['name'],
                    'url'   => $this->routeUrl('/forms/entries/'.$meta['name'])
                ];
            }
        }
    });


    // dashboard widgets
    $this->on("admin.dashboard.widgets", function($widgets) {

        $forms = $this->module("forms")->forms(true);

        $widgets[] = [
            "name"    => "forms",
            "content" => $this->view("forms:views/widgets/dashboard.php", compact('forms')),
            "area"    => 'aside-left'
        ];

    }, 100);


});
