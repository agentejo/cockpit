<?php

$this->module("collections")->extend([

    'createCollection' => function($name, $data = []) {

        if (!trim($name)) {
            return false;
        }

        $configpath = $this->app->path('#storage:').'/collections';

        if (!$this->app->path('#storage:collections')) {

            if (!$this->app->helper('fs')->mkdir($configpath)) {
                return false;
            }
        }

        if ($this->exists($name)) {
            return false;
        }

        $time = time();

        $collection = array_replace_recursive([
            'name'      => $name,
            'label'     => $name,
            '_id'       => uniqid($name),
            'fields'    => [],
            'sortable'  => false,
            'in_menu'   => false,
            '_created'  => $time,
            '_modified' => $time
        ], $data);

        $export = var_export($collection, true);

        if (!$this->app->helper('fs')->write("#storage:collections/{$name}.collection.php", "<?php\n return {$export};")) {
            return false;
        }

        $this->app->trigger('collections.createcollection', [$collection]);

        return $collection;
    },

    'updateCollection' => function($name, $data) {

        $metapath = $this->app->path("#storage:collections/{$name}.collection.php");

        if (!$metapath) {
            return false;
        }

        $data['_modified'] = time();

        $collection  = include($metapath);
        $collection  = array_merge($collection, $data);
        $export = var_export($collection, true);

        if (!$this->app->helper('fs')->write($metapath, "<?php\n return {$export};")) {
            return false;
        }

        $this->app->trigger('collections.updatecollection', [$collection]);
        $this->app->trigger("collections.updatecollection.{$name}", [$collection]);

        return $collection;
    },

    'saveCollection' => function($name, $data) {

        if (!trim($name)) {
            return false;
        }

        return isset($data['_id']) ? $this->updateCollection($name, $data) : $this->createCollection($name, $data);
    },

    'removeCollection' => function($name) {

        if ($collection = $this->collection($name)) {

            $collection = $collections["_id"];

            $this->app->helper("fs")->delete("#storage:collections/{$name}.collection.php");
            $this->app->storage->dropCollection("collections/{$collection}");

            $this->app->trigger('collections.removecollection', [$name]);
            $this->app->trigger("collections.removecollection.{$name}", [$name]);

            return true;
        }

        return false;
    },

    'collections' => function($extended = false) {

        $stores = [];

        foreach($this->app->helper("fs")->ls('*.collection.php', '#storage:collections') as $path) {

            $store = include($path->getPathName());

            if ($extended) {
                $store['itemsCount'] = $this->count($store['name']);
            }

            $stores[$store['name']] = $store;
        }

        return $stores;
    },

    'exists' => function($name) {
        return $this->app->path("#storage:collections/{$name}.collection.php");
    },

    'collection' => function($name) {

        static $collections; // cache

        if (is_null($collections)) {
            $collections = [];
        }

        if (!is_string($name)) {
            return false;
        }

        if (!isset($collections[$name])) {

            $collections[$name] = false;

            if ($path = $this->exists($name)) {
                $collections[$name] = include($path);
            }
        }

        return $collections[$name];
    },

    'entries' => function($name) use($app) {

        $collections = $this->collection($name);

        if (!$collections) return false;

        $collection = $collections["_id"];

        return $this->app->storage->getCollection("collections/{$collection}");
    },

    'find' => function($collection, $options = []) {

        $collections = $this->collection($collection);

        if (!$collections) return false;

        $name       = $collection;
        $collection = $collections["_id"];

        // sort by custom order if collection is sortable
        if (isset($collections['sortable']) && $collections['sortable'] && !isset($options['sort'])) {
            $options['sort'] = ['_order' => 1];
        }

        $this->app->trigger('collections.find.before', [$name, &$options, false]);
        $this->app->trigger("collections.find.before.{$name}", [$name, &$options, false]);

        $entries = (array)$this->app->storage->find("collections/{$collection}", $options);

        $this->app->trigger('collections.find.after', [$name, &$entries, false]);
        $this->app->trigger("collections.find.after.{$name}", [$name, &$entries, false]);

        return $entries;
    },

    'findOne' => function($collection, $criteria = [], $projection = null) {

        $collections = $this->collection($collection);

        if (!$collections) return false;

        $name       = $collection;
        $collection = $collections["_id"];

        $this->app->trigger('collections.find.before', [$name, &$criteria, true]);
        $this->app->trigger("collections.find.before.{$name}", [$name, &$criteria, true]);

        $entry = $this->app->storage->findOne("collections/{$collection}", $criteria, $projection);

        $this->app->trigger('collections.find.after', [$name, &$entry, true]);
        $this->app->trigger("collections.find.after.{$name}", [$name, &$entry, true]);

        return $entry;
    },

    'save' => function($collection, $data) {

        $collections = $this->collection($collection);

        if (!$collections) return false;

        $name       = $collection;
        $collection = $collections["_id"];
        $data       = isset($data[0]) ? $data : [$data];
        $return     = [];
        $modified   = time();

        foreach($data as $entry) {

            $isUpdate = isset($entry["_id"]);

            $entry['_modified'] = $modified;

            if (isset($collections['fields'])) {

                foreach($collections['fields'] as $field) {

                    // skip missing fields on update
                    if (!isset($entry[$field['name']]) && $isUpdate) {
                        continue;
                    }

                    if (!isset($entry[$field['name']])) {
                        $value = isset($field['default']) ? $field['default'] : '';
                    } else {
                        $value = $entry[$field['name']];
                    }

                    switch($field['type']) {

                        case 'string':
                        case 'text':
                            $value = (string)$value;
                            break;

                        case 'boolean':

                            if ($value === 'true' || $value === 'false') {
                                $value = $value === 'true' ? true:false;
                            } else {
                                $value = $value ? true:false;
                            }

                            break;

                        case 'number':
                            $value = is_numeric($value) ? $value:0;
                            break;

                        case 'url':
                            $value = filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ? $value:null;
                            break;

                        case 'email':
                            $value = filter_var($value, FILTER_VALIDATE_EMAIL) ? $value:null;
                            break;

                        case 'password':

                            if ($value) {

                                $value = $this->app->hash($value);
                            }

                            break;
                    }

                    if ($isUpdate && $field['type'] == 'password' && !$value && isset($entry[$field['name']])) {
                        unset($entry[$field['name']]);
                    } else {
                        $entry[$field['name']] = $value;
                    }

                }
            }

            if (!$isUpdate) {
                $entry["_created"] = $entry["_modified"];
            }

            $this->app->trigger('collections.save.before', [$name, &$entry, $isUpdate]);
            $this->app->trigger("collections.save.before.{$name}", [$name, &$entry, $isUpdate]);

            $ret = $this->app->storage->save("collections/{$collection}", $entry);

            $this->app->trigger('collections.save.after', [$name, &$entry, $isUpdate]);
            $this->app->trigger("collections.save.after.{$name}", [$name, &$entry, $isUpdate]);

            $return[] = $ret ? $entry : false;
        }

        return count($return) == 1 ? $return[0] : $return;
    },

    'remove' => function($collection, $criteria) {

        $collections = $this->collection($collection);

        if (!$collections) return false;

        $name       = $collection;
        $collection = $collections["_id"];

        $this->app->trigger('collections.remove.before', [$name, &$criteria]);
        $this->app->trigger("collections.remove.before.{$name}", [$name, &$criteria]);

        $result = $this->app->storage->remove("collections/{$collection}", $criteria);

        $this->app->trigger('collections.remove.after', [$name, $result]);
        $this->app->trigger("collections.remove.after.{$name}", [$name, $result]);

        return $result;
    },

    'count' => function($collection, $criteria = []) {

        $collections = $this->collection($collection);

        if (!$collections) return false;

        $collection = $collections["_id"];

        return $this->app->storage->count("collections/{$collection}", $criteria);
    }
]);


// REST
if (COCKPIT_REST) {

    $app->on('cockpit.rest.init', function($routes) {
        $routes['collections'] = 'Collections\\Controller\\RestApi';
    });
}


// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_REST) {

    include_once(__DIR__.'/admin.php');
}
