<?php

// API


$this->module("collections")->extend([

    "collection" => function($name) use($app) {

        static $collections;

        if (null === $collections) {
            $collections = [];
        }

        if (!isset($collections[$name])) {

            $collection = $app->db->findOne("common/collections", ["name"=>$name]);

            if($collection) {
                $collection = "collection".$collection["_id"];
                $collections[$name] = $app->db->getCollection("collections/{$collection}");
            }
        }

        return isset($collections[$name]) ? $collections[$name] : null;
    },

    "collectionById" => function($colid) use($app) {

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

    "populate" => function($collection, $resultset) use($app) {

        static $cache;

        if (null === $cache) {
            $cache = [];
        }

        // check if resultset is a cursor object
        if (is_object($resultset)) {
            if(is_a($resultset, 'MongoLite\\Cursor')) $resultset = $resultset->toArray();
            if(is_a($resultset, 'MongoCursor')) $resultset = iterator_to_array($resultset);
        }

        if (!count($resultset)) {
            return $resultset;
        }

        $collection = $app->db->findOne("common/collections", ["name"=>$collection]);

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

    "populateOne" => function($collection, $item) use($app) {

        if (!$item) {
            return $item;
        }

        $item = $this->populate($collection, [$item]);

        return $item[0];
    }
]);

if(!function_exists("collection")) {
    function collection($name) {
        return cockpit("collections")->collection($name);
    }
}

if(!function_exists("collection_populate")) {
    function collection_populate($collection, $resultset) {
        return cockpit("collections")->populate($collection, $resultset);
    }

    function collection_populate_one($collection, $item) {
        return cockpit("collections")->populateOne($collection, $item);
    }
}

//rest
$app->on("cockpit.rest.init", function($routes) {
    $routes["collections"] = 'Collections\\Controller\\RestApi';
});

// ADMIN

if(COCKPIT_ADMIN && !COCKPIT_REST) {


    $app->on("admin.init", function() {

        if(!$this->module("auth")->hasaccess("Collections", ['manage.collections', 'manage.entries'])) return;

        // bind controllers
        $this->bindClass("Collections\\Controller\\Collections", "collections");
        $this->bindClass("Collections\\Controller\\Api", "api/collections");

        $this("admin")->menu("top", [
            "url"    => $this->routeUrl("/collections"),
            "label"  => '<i class="uk-icon-list"></i>',
            "title"  => $this("i18n")->get("Collections"),
            "active" => (strpos($this["route"], '/collections') === 0)
        ], 5);

        // handle global search request
        $this->on("cockpit.globalsearch", function($search, $list) {

            foreach ($this->db->find("common/collections") as $c) {
                if(stripos($c["name"], $search)!==false){
                    $list[] = [
                        "title" => '<i class="uk-icon-list"></i> '.$c["name"],
                        "url"   => $this->routeUrl('/collections/entries/'.$c["_id"])
                    ];
                }
            }
        });

    });

    $app->on("admin.dashboard.aside", function() {

        if(!$this->module("auth")->hasaccess("Collections", ['manage.collections', 'manage.entries'])) return;

        $title       = $this("i18n")->get("Collections");
        $badge       = $this->db->getCollection("common/collections")->count();
        $collections = $this->db->find("common/collections", ["limit"=> 3, "sort"=>["created"=>-1] ])->toArray();

        $this->renderView("collections:views/dashboard.php with cockpit:views/layouts/dashboard.widget.php", compact('title', 'badge', 'collections'));
    });

    // acl
    $this("acl")->addResource("Collections", ['manage.collections', 'manage.entries']);
}