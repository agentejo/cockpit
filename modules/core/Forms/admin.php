<?php

// ACL
$app("acl")->addResource("forms", ['manage.forms']);


$app->on('admin.init', function() {

    if (!$this->module('cockpit')->hasaccess('forms', ['manage.forms'])) {
        return;
    }

    // bind admin routes /forms/*
    $this->bindClass('Forms\\Controller\\Admin', 'forms');

    // add to modules menu
    $this('admin')->addMenuItem('modules', [
        'label' => 'Forms',
        'icon'  => 'inbox',
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
    $this->on("admin.dashboard.aside-left", function() {
        $forms = $this->module("forms")->forms(true);
        $this->renderView("forms:views/widgets/dashboard.php", compact('forms'));
    }, 100);


});
