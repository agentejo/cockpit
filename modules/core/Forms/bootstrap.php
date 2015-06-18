<?php


$this->module("forms")->extend([

    'createForm' => function($name, $data = []) {

        if (!trim($name)) {
            return false;
        }

        $configpath = $this->app->path('#storage:').'/forms';

        if (!$this->app->path('#storage:forms')) {

            if (!$this->app->helper('fs')->mkdir($configpath)) {
                return false;
            }
        }

        if ($this->exists($name)) {
            return false;
        }

        $time = time();

        $form = array_replace_recursive([
            '_id'       => uniqid($name),
            'name'      => $name,
            'label'     => $name,
            'save_entry' => true,
            'email_forward' => '',
            '_created'  => $time,
            '_modified' => $time
        ], $data);

        $export = var_export($form, true);

        if (!$this->app->helper('fs')->write("#storage:forms/{$name}.form.php", "<?php\n return {$export};")) {
            return false;
        }

        return $form;
    },

    'updateForm' => function($name, $data) {

        $metapath = $this->app->path("#storage:forms/{$name}.form.php");

        if (!$metapath) {
            return false;
        }

        $data['_modified'] = time();

        $form  = include($metapath);
        $form  = array_merge($form, $data);
        $export = var_export($form, true);

        if (!$this->app->helper('fs')->write($metapath, "<?php\n return {$export};")) {
            return false;
        }

        return $form;
    },

    'saveForm' => function($name, $data) {

        if (!trim($name)) {
            return false;
        }

        return isset($data['_id']) ? $this->updateForm($name, $data) : $this->createForm($name, $data);
    },

    'removeForm' => function($name) {

        if ($form = $this->form($name)) {

            $form = $forms["_id"];

            $this->app->helper("fs")->delete("#storage:forms/{$name}.form.php");
            $this->app->storage->dropform("forms/{$form}");

            return true;
        }

        return false;
    },

    'forms' => function($extended = false) {

        $stores = [];

        foreach($this->app->helper("fs")->ls('*.form.php', '#storage:forms') as $path) {

            $store = include($path->getPathName());

            if ($extended) {
                $store['itemsCount'] = $this->count($store['name']);
            }

            $stores[$store['name']] = $store;
        }

        return $stores;
    },

    'exists' => function($name) {
        return $this->app->path("#storage:forms/{$name}.form.php");
    },

    'form' => function($name) {

        static $forms; // cache

        if (is_null($forms)) {
            $forms = [];
        }

        if (!is_string($name)) {
            return false;
        }

        if (!isset($forms[$name])) {

            $forms[$name] = false;

            if ($path = $this->exists($name)) {
                $forms[$name] = include($path);
            }
        }

        return $forms[$name];
    },

    'entries' => function($name) use($app) {

        $forms = $this->form($name);

        if (!$forms) return false;

        $form = $forms["_id"];

        return $this->app->storage->getform("forms/{$form}");
    },

    'find' => function($form, $options = []) {

        $forms = $this->form($form);

        if (!$forms) return false;

        $form = $forms["_id"];

        // sort by custom order if form is sortable
        if (isset($forms['sortable']) && $forms['sortable'] && !isset($options['sort'])) {
            $options['sort'] = ['_order' => 1];
        }

        return (array)$this->app->storage->find("forms/{$form}", $options);
    },

    'findOne' => function($form, $criteria = [], $projection = null) {

        $forms = $this->form($form);

        if (!$forms) return false;

        $form = $forms["_id"];

        return $this->app->storage->findOne("forms/{$form}", $criteria, $projection);
    },

    'save' => function($form, $data) {

        $forms = $this->form($form);

        if (!$forms) return false;

        $form = $forms["_id"];
        $data       = isset($data[0]) ? $data : [$data];
        $return     = [];
        $modified   = time();

        foreach($data as $entry) {

            $isUpdate = isset($entry["_id"]);

            $entry['_modified'] = $modified;

            if (!$isUpdate) {
                $entry["_created"] = $entry["_modified"];
            }

            $ret = $this->app->storage->save("forms/{$form}", $entry);

            $return[] = $ret ? $entry : false;
        }

        return count($return) == 1 ? $return[0] : $return;
    },

    'remove' => function($form, $criteria) {

        $forms = $this->form($form);

        if (!$forms) return false;

        $form = $forms["_id"];

        return $this->app->storage->remove("forms/{$form}", $criteria);
    },

    'count' => function($form, $criteria = []) {

        $forms = $this->form($form);

        if (!$forms) return false;

        $form = $forms["_id"];

        return $this->app->storage->count("forms/{$form}", $criteria);
    }
]);



// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_REST) {

    include_once(__DIR__.'/admin.php');
}