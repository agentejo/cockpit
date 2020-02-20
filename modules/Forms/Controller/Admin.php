<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

        $this->app->helper('admin')->favicon = [
            'path' => 'forms:icon.svg',
            'color' => $form['color']
        ];

        $view = 'forms:views/entries.php';

        if ($override = $this->app->path('#config:forms/'.$form['name'].'/views/entries.php')) {
            $view = $override;
        }

        return $this->render($view, compact('form', 'count'));
    }

    public function find() {

        $form = $this->app->param('form');
        $options    = $this->app->param('options');

        if (!$form) return false;

        $entries = $this->app->module('forms')->find($form, $options);
        $count   = $this->app->module('forms')->count($form, isset($options['filter']) ? $options['filter'] : []);
        $pages   = isset($options['limit']) ? ceil($count / $options['limit']) : 1;
        $page    = 1;

        if ($pages > 1 && isset($options['skip'])) {
            $page = ceil($options['skip'] / $options['limit']) + 1;
        }

        return compact('entries', 'count', 'pages', 'page');
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
