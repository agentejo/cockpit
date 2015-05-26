<?php

namespace Collections\Controller;


class Admin extends \Cockpit\AuthController {


    public function index() {

        return $this->render('collections:views/index.php');
    }

    public function collection($name = null) {

        $collection = [ 'name'=>'', 'fields'=>[], 'sortable' => false ];

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

        return $this->render('collections:views/entries.php', compact('collection', 'count'));
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

        return $this->render('collections:views/entry.php', compact('collection', 'entry'));
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
}
