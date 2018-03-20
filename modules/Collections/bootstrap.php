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

    'saveCollection' => function($name, $data, $rules = null) {

        if (!trim($name)) {
            return false;
        }

        if ($rules) {

            foreach (['create', 'read', 'update', 'delete'] as $method) {

                if (isset($rules[$method])) {

                    $code = trim($rules[$method]);

                    if ($code == '<?php') {
                        $code .= "\n\n";
                    }

                    if (strpos($code, '<?php')!==0) {
                        $code = "<?php\n\n{$code}";
                    }

                    $this->app->helper('fs')->write("#storage:collections/rules/{$name}.{$method}.php", $code);
                }
            }
        }

        return isset($data['_id']) ? $this->updateCollection($name, $data) : $this->createCollection($name, $data);
    },

    'removeCollection' => function($name) {

        if ($collection = $this->collection($name)) {

            $this->app->helper("fs")->delete("#storage:collections/{$name}.collection.php");

            // remove rules
            foreach (['create', 'read', 'update', 'delete'] as $method) {
                $this->app->helper("fs")->delete("#storage:collections/rules/{$name}.{$method}.php");
            }

            $this->app->storage->dropCollection("collections/{$collection['_id']}");

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

        // check rule
        $context = new \stdClass();
        $context->options = $options;

        if (_check_collection_rule($_collection, 'read', $context) === false) {
            return [];
        } else {
            $options = $context->options;
        }

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
        $options    = [
            'filter'       => $criteria,
            'fields'       => $projection,
            'populate'     => $populate,
            'fieldsFilter' => $fieldsFilter,
            'limit'        => 1
        ];

        $entries = $this->find($name, $options);

        return isset($entries[0]) ? $entries[0] : null;
    },

    'save' => function($collection, $data, $options = []) {

        $options = array_merge(['revision' => false], $options);

        $_collection = $this->collection($collection);

        if (!$_collection) return false;

        $name       = $collection;
        $collection = $_collection['_id'];
        $data       = isset($data[0]) ? $data : [$data];
        $return     = [];
        $modified   = time();

        foreach($data as &$entry) {

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

                    if (!$isUpdate && isset($field['required']) && $field['required'] && !$value) {
                        // Todo
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

            // check rule
            $context = _check_collection_rule($_collection, 'read', [
                'options' => $options,
                'entry'   => $entry
            ]);

            if ($context === false) {
                continue;
            } else {
                $entry   = $context->entry;
                $options = $context->options;
            }

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

        // check rule
        $context = _check_collection_rule($_collection, 'remove', ['options' => ['filter' => $criteria]]);

        if ($context === false) {
            return false;
        } else {
            $criteria = $context->options['filter'];
        }

        $result = $this->app->storage->remove("collections/{$collection}", $criteria);

        $this->app->trigger('collections.remove.after', [$name, $result]);
        $this->app->trigger("collections.remove.after.{$name}", [$name, $result]);

        return $result;
    },

    'count' => function($collection, $criteria = []) {

        $_collection = $this->collection($collection);

        if (!$_collection) return false;

        $collection = $_collection['_id'];

        // check rule
        $context = _check_collection_rule($_collection, 'read', ['options' => ['filter' => $criteria]]);

        if ($context === false) {
            return 0;
        } else {
            $criteria = $context->options['filter'];
        }

        return $this->app->storage->count("collections/{$collection}", $criteria);
    },

    '_resolveLinkedItem' => function($link, $_id, $fieldsFilter = []) {

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

        return cockpit_populate_collection($items, $maxlevel, 0, $fieldsFilter);
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

    if (is_numeric($maxlevel) && $maxlevel > -1 && $level > ($maxlevel+1)) {
        return $items;
    }

    foreach ($items as $k => &$v) {

        if (is_array($items[$k])) {
            $items[$k] = cockpit_populate_collection($items[$k], $maxlevel, ($level + 1), $fieldsFilter);
        }

        if (isset($v['_id'], $v['link'])) {
            $link = $v['link'];
            $items[$k] = cockpit('collections')->_resolveLinkedItem($v['link'], $v['_id'], $fieldsFilter);
            $items[$k]['_link'] = $link;
            $items[$k] = cockpit_populate_collection($items[$k], $maxlevel, $level, $fieldsFilter);
        }
    }

    return $items;
}

function _check_collection_rule($collection, $rule, $_context = null) {

    $context = (object) $_context;

    if (isset($collection['rules'][$rule]['enabled']) && $collection['rules'][$rule]['enabled']) {

        $_rulefile = cockpit()->path("#storage:collections/rules/{$collection['name']}.{$rule}.php");

        if ($_rulefile) {

            $context->user = cockpit()->module('cockpit')->getUser();
            $ret = null;

            try {
                $ret = include($_rulefile);
            } catch(\Throwable $e) {

                if (cockpit()->retrieve('config/debug')) {
                    echo $e;
                }
            }

            if (!is_null($ret) && is_numeric($ret) && $ret >= 400) {
                cockpit()->stop($ret);
            }

            return $ret === false ? false : $context;
        }
    }

    return $context;
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

    // allow access to public collections
    $app->on('cockpit.api.authenticate', function($data) {

        if ($data['user'] || $data['resource'] != 'collections') return;

        if (isset($data['query']['params'][1])) {

            $collection = $this->module('collections')->collection($data['query']['params'][1]);

            if ($collection && isset($collection['acl']['public'])) {
                $data['authenticated'] = true;
                $data['user'] = ['_id' => null, 'group' => 'public'];
            }
        }
    });
}


// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {

    include_once(__DIR__.'/admin.php');
}
