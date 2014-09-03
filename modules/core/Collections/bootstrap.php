<?php

// API

$this->module("collections")->extend([

    'collection' => function($name) use($app) {

        static $collections;

        if (null === $collections) {
            $collections = [];
        }

        if (!isset($collections[$name])) {

            $collection = $app->db->findOne('common/collections', ['name'=>$name]);

            if ($collection) {
                $collection = 'collection'.$collection["_id"];
                $collections[$name] = $app->db->getCollection("collections/{$collection}");
            }
        }

        return isset($collections[$name]) ? $collections[$name] : null;
    },

    'collectionById' => function($colid) use($app) {

        static $collections;

        if (null === $collections) {
            $collections = [];
        }

        if (!isset($collections[$colid])) {

            $collection          = "collection{$colid}";
            $collections[$colid] = $app->db->getCollection("collections/{$collection}");

        }

        return $collections[$colid];
    },

    'collections'=> function($options = []) {

        $return      = [];
        $collections = $app->db->find('common/collections', $options)->toArray();

        foreach ($collections as $collection) {
            $return[$collection['name']] = $this->collectionById($collection['_id']);
        }

        return $return;
    },

    'get_collection_by_slug' => function($slug) use($app) {

        $collection = $app->db->findOne('common/collections', ['slug'=>$slug]);

        if ($collection) {
            $collection = "collection".$collection["_id"];
            return $app->db->getCollection("collections/{$collection}");
        }

        return null;
    },

    'group' => function($group, $sort = null) use($app) {

        return $this->collections(['filter' =>['group' => $group], 'sort'=> $sort]);
    },

    'populate' => function($collection, $resultset) use($app) {

        static $cache;

        if (null === $cache) {
            $cache = [];
        }

        // check if resultset is a cursor object
        if (is_object($resultset)) {
            if (is_a($resultset, 'MongoLite\\Cursor')) $resultset = $resultset->toArray();
            if (is_a($resultset, 'MongoCursor')) $resultset = iterator_to_array($resultset);
        }

        if (!count($resultset)) {
            return $resultset;
        }

        $collection = $app->db->findOne('common/collections', ['name'=>$collection]);

        if (!$collection) {
            return $resultset;
        }

        $hasOne  = [];
        $hasMany = [];

        foreach($collection['fields'] as &$field) {

            if ($field['type'] == 'link-collection') {

                if (isset($field['multiple']) && $field['multiple']) {
                    $hasMany[$field['name']] = $field['collection'];
                } else {
                    $hasOne[$field['name']] = $field['collection'];
                }
            }
        }


        foreach ($resultset as &$doc) {

            // resolve hasOne
            foreach ($hasOne as $f => $colid) {

                if (isset($doc[$f]) && $doc[$f]) {

                    if (!isset($cache[$colid][$doc[$f]])) {
                        $cache[$colid][$doc[$f]] = $this->collectionById($colid)->findOne(['_id' => $doc[$f]]);
                    }

                    $doc[$f] = $cache[$colid][$doc[$f]];
                }
            }

            // resolve hasMany
            foreach ($hasMany as $f => $colid) {

                if (isset($doc[$f]) && $doc[$f] && is_array($doc[$f])) {

                    $col = $this->collectionById($colid);

                    foreach ($doc[$f] as $index => $_id) {

                        if (!isset($cache[$colid][$_id])) {

                            $cache[$colid][$_id] = $col->findOne(['_id' => $_id]);
                        }

                        $doc[$f][$index] = $cache[$colid][$_id];
                    }
                }
            }
        }

        return $resultset;
    },

    'populateOne' => function($collection, $item) use($app) {

        if (!$item) {
            return $item;
        }

        $item = $this->populate($collection, [$item]);

        return $item[0];
    }
]);

if (!function_exists('collection')) {
    function collection($name) {
        return cockpit('collections')->collection($name);
    }
}

if (!function_exists('collection_populate')) {
    function collection_populate($collection, $resultset) {
        return cockpit('collections')->populate($collection, $resultset);
    }

    function collection_populate_one($collection, $item) {
        return cockpit('collections')->populateOne($collection, $item);
    }
}

// REST
$app->on('cockpit.rest.init', function($routes) {
    $routes["collections"] = 'Collections\\Controller\\RestApi';
});

// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_REST) include_once(__DIR__.'/admin.php');
