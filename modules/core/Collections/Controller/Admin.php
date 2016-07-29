<?php

namespace Collections\Controller;


class Admin extends \Cockpit\AuthController {


    public function index() {

        return $this->render('collections:views/index.php');
    }

    public function collection($name = null) {

        $collection = [ 'name'=>'', 'label' => '', 'color'=>'', 'fields'=>[], 'sortable' => false, 'in_menu' => false ];

        if ($name) {

            $collection = $this->module('collections')->collection($name);

            if (!$collection) {
                return false;
            }
        }

        return $this->render('collections:views/collection.php', compact('collection'));
    }

    public function entries($collection) {

        $collection = $this->module('collections')->collection($collection);

        if (!$collection) {
            return false;
        }

        $count = $this->module('collections')->count($collection['name']);

        $collection = array_merge([
            'sortable' => false
        ], $collection);

        $view = 'collections:views/entries.php';

        if ($override = $this->app->path('config:collections/'.$collection['name'].'views/entries.php')) {
            $view = $path;
        }

        return $this->render($view, compact('collection', 'count'));
    }

    public function entry($collection, $id = null) {

        $collection = $this->module('collections')->collection($collection);
        $entry      = new \ArrayObject([]);

        if (!$collection) {
            return false;
        }

        if ($id) {

            $entry = $this->module('collections')->findOne($collection['name'], ['_id' => $id]);

            if (!$entry) {
                return false;
            }
        }

        $view = 'collections:views/entry.php';

        if ($override = $this->app->path('config:collections/'.$collection['name'].'views/entry.php')) {
            $view = $override;
        }

        return $this->render($view, compact('collection', 'entry'));
    }

    public function export($collection) {

        if (!$this->app->module("cockpit")->hasaccess("collections", 'manage.collections')) {
            return false;
        }

        $collection = $this->module('collections')->collection($collection);

        if (!$collection) return false;

        $entries = $this->module('collections')->find($collection['name']);

        return json_encode($entries, JSON_PRETTY_PRINT);
    }

    public function find() {

        $collection = $this->app->param('collection');
        $options    = $this->app->param('options');

        if (!$collection) return false;

        $entries = $this->app->module('collections')->find($collection, $options);
        $count   = $this->app->module('collections')->count($collection, isset($options['filter']) ? $options['filter'] : []);
        $pages   = isset($options['limit']) ? ceil($count / $options['limit']) : 1;
        $page    = 1;

        if ($pages > 1 && isset($options['skip'])) {
            $page = ceil($options['skip'] / $options['limit']) + 1;
        }

        return compact('entries', 'count', 'pages', 'page');
    }
}
