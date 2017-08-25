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
        $export      = var_export($collection, true);

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

        $_collection = $this->collection($name);

        if (!$_collection) return false;

        $collection = $_collection['_id'];

        return $this->app->storage->getCollection("collections/{$collection}");
    },

    'find' => function($collection, $options = []) {

        $_collection = $this->collection($collection);

        if (!$_collection) return false;

        $name       = $collection;
        $collection = $_collection['_id'];

        // sort by custom order if collection is sortable
        if (isset($_collection['sortable']) && $_collection['sortable'] && !isset($options['sort'])) {
            $options['sort'] = ['_order' => 1];
        }

        $this->app->trigger('collections.find.before', [$name, &$options, false]);
        $this->app->trigger("collections.find.before.{$name}", [$name, &$options, false]);

        $entries = (array)$this->app->storage->find("collections/{$collection}", $options);

        $fieldsFilter = [];

        if (isset($options['fieldsFilter']) && is_array($options['fieldsFilter'])) {
            $fieldsFilter = $options['fieldsFilter'];
        }

        if (isset($options['user']) && $options['user']) {
            $fieldsFilter['user'] = $options['user'];
        }

        if (isset($options['lang']) && $options['lang']) {
            $fieldsFilter['lang'] = $options['lang'];
        }

        if (count($fieldsFilter)) {
           $entries = $this->_filterFields($entries, $_collection, $fieldsFilter);
        }

        if (isset($options['populate']) && $options['populate']) {
            $entries = $this->_populate($entries, is_numeric($options['populate']) ? intval($options['populate']) : false, 0, $fieldsFilter);
        }

        $this->app->trigger('collections.find.after', [$name, &$entries, false]);
        $this->app->trigger("collections.find.after.{$name}", [$name, &$entries, false]);

        return $entries;
    },

    'findOne' => function($collection, $criteria = [], $projection = null, $populate = false, $fieldsFilter = []) {

        $_collection = $this->collection($collection);

        if (!$_collection) return false;

        $name       = $collection;
        $collection = $_collection['_id'];

        $this->app->trigger('collections.find.before', [$name, &$criteria, true]);
        $this->app->trigger("collections.find.before.{$name}", [$name, &$criteria, true]);

        $entry = $this->app->storage->findOne("collections/{$collection}", $criteria, $projection);

        if (count($fieldsFilter)) {
           $entry = $this->_filterFields($entry, $_collection, $fieldsFilter);
        }

        if ($entry && $populate) {
           $entry = $this->_populate([$entry], is_numeric($populate) ? intval($populate) : false, 0, $fieldsFilter);
           $entry = $entry[0];
        }

        $this->app->trigger('collections.find.after', [$name, &$entry, true]);
        $this->app->trigger("collections.find.after.{$name}", [$name, &$entry, true]);

        return $entry;
    },

    'save' => function($collection, $data, $options = []) {

        $options = array_merge([
            'revision' => false
        ], $options);

        $_collection = $this->collection($collection);

        if (!$_collection) return false;

        $name       = $collection;
        $collection = $_collection['_id'];
        $data       = isset($data[0]) ? $data : [$data];
        $return     = [];
        $modified   = time();

        foreach($data as $entry) {

            $isUpdate = isset($entry['_id']);

            $entry['_modified'] = $modified;

            if (isset($_collection['fields'])) {

                foreach($_collection['fields'] as $field) {

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

            if ($ret && $options['revision']) {
                $this->app->helper('revisions')->add($entry['_id'], $entry, "collections/{$collection}", true);
            }

            $return[] = $ret ? $entry : false;
        }

        return count($return) == 1 ? $return[0] : $return;
    },

    'remove' => function($collection, $criteria) {

        $_collection = $this->collection($collection);

        if (!$_collection) return false;

        $name       = $collection;
        $collection = $_collection['_id'];

        $this->app->trigger('collections.remove.before', [$name, &$criteria]);
        $this->app->trigger("collections.remove.before.{$name}", [$name, &$criteria]);

        $result = $this->app->storage->remove("collections/{$collection}", $criteria);

        $this->app->trigger('collections.remove.after', [$name, $result]);
        $this->app->trigger("collections.remove.after.{$name}", [$name, $result]);

        return $result;
    },

    'count' => function($collection, $criteria = []) {

        $_collection = $this->collection($collection);

        if (!$_collection) return false;

        $collection = $_collection['_id'];

        return $this->app->storage->count("collections/{$collection}", $criteria);
    },

    '_resolveLinedkItem' => function($link, $_id, $fieldsFilter = []) {

        static $cache;

        if (null === $cache) {
            $cache = [];
        }

        if (!isset($cache[$link])) {
            $cache[$link] = [];
        }

        if (!isset($cache[$link][$_id])) {
            $cache[$link][$_id] = $this->findOne($link, ['_id' => $_id], null, false, $fieldsFilter);
        }

        return $cache[$link][$_id];
    },

    '_populate' => function($items, $maxlevel=-1, $level=0, $fieldsFilter = []) {

        if (!is_array($items)) {
            return $items;
        }
        return cockpit_populate_collection($items, -1, 0, $fieldsFilter);
    },

    '_filterFields' => function($items, $collection, $filter) {

        static $cache;
        static $languages;

        if (null === $items) {
            return $items;
        }

        $single = false;

        if (!isset($items[0]) && count($items)) {
            $items = [$items];
            $single = true;
        }

        $filter = array_merge([
            'user' => false,
            'lang' => false,
            'ignoreDefaultFallback' => false
        ], $filter);

        extract($filter);
        
        if (null === $cache) {
            $cache = [];
        }

        if (null === $languages) {
                
            $languages = [];

            foreach($this->app->retrieve('config/languages', []) as $key => $val) {
                if (is_numeric($key)) $key = $val;
                $languages[] = $key;
            }
        }

        if (is_string($collection)) {
            $collection = $this->collection($collection);
        }

        if (!isset($cache[$collection['name']])) {

            $fields = [
                "acl" => [],
                "localize" => []
            ];

            foreach ($collection["fields"] as $field) {

                if (isset($field['acl']) && is_array($field['acl']) && count($field['acl'])) {
                    $fields['acl'][$field['name']] = $field['acl'];
                }

                if (isset($field['localize']) && $field['localize']) {
                    $fields['localize'][$field['name']] = true;
                }
            }

            $cache[$collection['name']] = $fields;
        }

        if ($user && count($cache[$collection['name']]['acl'])) {
            
            $aclfields = $cache[$collection['name']]['acl'];
            $items     = array_map(function($entry) use($user, $aclfields, $languages) {
                
                foreach ($aclfields as $name => $acl) {

                    if (!( in_array($user['group'], $acl) || in_array($user['_id'], $acl) )) {
                        
                        unset($entry[$name]);

                        if (count($languages)) {

                            foreach($languages as $l) {
                                if (isset($entry["{$name}_{$l}"])) {
                                    unset($entry["{$name}_{$l}"]);
                                    unset($entry["{$name}_{$l}_slug"]);
                                }
                            }
                        }
                    }
                }

                return $entry;

            }, $items);
        }

        if ($lang && count($languages) && count($cache[$collection['name']]['localize'])) {

            $localfields = $cache[$collection['name']]['localize'];
            $items = array_map(function($entry) use($localfields, $lang, $languages, $ignoreDefaultFallback) {
                
                foreach ($localfields as $name => $local) {

                    foreach($languages as $l) {
                        
                        if (isset($entry["{$name}_{$l}"])) {

                            if ($l == $lang) {
                                
                                $entry[$name] = $entry["{$name}_{$l}"];

                                if (isset($entry["{$name}_{$l}_slug"])) {
                                    $entry["{$name}_slug"] = $entry["{$name}_{$l}_slug"];
                                }
                            }

                            unset($entry["{$name}_{$l}"]);
                            unset($entry["{$name}_{$l}_slug"]);

                        } elseif ($l == $lang && $ignoreDefaultFallback) {

                            if ($ignoreDefaultFallback === true || (is_array($ignoreDefaultFallback) && in_array($name, $ignoreDefaultFallback))) {
                                $entry[$name] = null;
                            }
                        }
                    }
                }

                return $entry;

            }, $items);
        }

        return $single ? $items[0] : $items;
    }
]);

function cockpit_populate_collection(&$items, $maxlevel = -1, $level = 0, $fieldsFilter = []) {

    if (!is_array($items)) {
        return $items;
    }

    if (is_numeric($maxlevel) && $maxlevel==$level) {
        return $items;
    }

    foreach ($items as $k => &$v) {

        if (is_array($items[$k])) {
            $items[$k] = cockpit_populate_collection($items[$k], $maxlevel, ++$level, $fieldsFilter);
        }

        if (isset($v['_id'], $v['link'])) {
            $items[$k] = cockpit('collections')->_resolveLinedkItem($v['link'], $v['_id'], $fieldsFilter);
        }
    }

    return $items;
}

// ACL
$app("acl")->addResource("collections", ['create', 'delete']);

$this->module("collections")->extend([

    'getCollectionsInGroup' => function($group = null, $extended = false) {

        if (!$group) {
            $group = $this->app->module('cockpit')->getGroup();
        }

        $_collections = $this->collections($extended);
        $collections = [];

        if ($this->app->module('cockpit')->isSuperAdmin()) {
            return $_collections;
        }

        foreach ($_collections as $collection => $meta) {

            if (isset($meta['acl'][$group]['entries_view']) && $meta['acl'][$group]['entries_view']) {
                $collections[$collection] = $meta;
            }
        }

        return $collections;
    },

    'hasaccess' => function($collection, $action, $group = null) {

        $collection = $this->collection($collection);

        if (!$collection) {
            return false;
        }

        if (!$group) {
            $group = $this->app->module('cockpit')->getGroup();
        }

        if ($this->app->module('cockpit')->isSuperAdmin($group)) {
            return true;
        }

        if (isset($collection['acl'][$group][$action])) {
            return $collection['acl'][$group][$action];
        }

        return false;
    }
]);


// REST
if (COCKPIT_API_REQUEST) {

    $app->on('cockpit.rest.init', function($routes) {
        $routes['collections'] = 'Collections\\Controller\\RestApi';
    });
}


// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {

    include_once(__DIR__.'/admin.php');
}
