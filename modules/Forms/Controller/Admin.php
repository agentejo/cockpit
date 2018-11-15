<?php

namespace Forms\Controller;

class Admin extends \Cockpit\AuthController {

    public function index() {

        $_forms = $this->module('forms')->forms(true);
        $forms  = [];

        foreach ($_forms as $name => $meta) {

           $forms[] = [
             'name' => $name,
             'label' => isset($meta['label']) && $meta['label'] ? $meta['label'] : $name,
             'meta' => $meta
           ];
        }

        // sort forms
        usort($forms, function($a, $b) {
            return mb_strtolower($a['label']) <=> mb_strtolower($b['label']);
        });

        return $this->render('forms:views/index.php', compact('forms'));
    }

    public function form($name = null) {

        $form = [ 'name'=>'', 'in_menu' => false ];

        if ($name) {

            $form = $this->module('forms')->form($name);

            if (!$form) {
                return false;
            }
        }

        return $this->render('forms:views/form.php', compact('form'));
    }

    public function entries($form) {

        $form = $this->module('forms')->form($form);

        if (!$form) {
            return false;
        }

        $count = $this->module('forms')->count($form['name']);

        $form = array_merge([
            'sortable' => false,
            'color' => '',
            'icon' => '',
            'description' => ''
        ], $form);

        $view = 'forms:views/entries.php';

        if ($override = $this->app->path('#config:forms/'.$form['name'].'/views/entries.php')) {
            $view = $override;
        }

        return $this->render($view, compact('form', 'count'));
    }

    public function export($form) {

        if (!$this->app->module('cockpit')->hasaccess('forms', 'manage')) {
            return false;
        }

        $form = $this->module('forms')->form($form);

        if (!$form) return false;

        $entries = $this->module('forms')->find($form['name']);

        return json_encode($entries, JSON_PRETTY_PRINT);
    }
}
