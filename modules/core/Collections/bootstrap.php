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
}

//rest
$app->on("cockpit.rest.init", function($routes) {
    $routes["collections"] = 'Collections\\Controller\\RestApi';
});

// ADMIN

if(COCKPIT_ADMIN && !COCKPIT_REST) {


    $app->on("admin.init", function() use($app){

        if(!$app->module("auth")->hasaccess("Collections", ['manage.collections', 'manage.entries'])) return;

        // bind controllers
        $app->bindClass("Collections\\Controller\\Collections", "collections");
        $app->bindClass("Collections\\Controller\\Api", "api/collections");

        $app("admin")->menu("top", [
            "url"    => $app->routeUrl("/collections"),
            "label"  => '<i class="uk-icon-list"></i>',
            "title"  => $app("i18n")->get("Collections"),
            "active" => (strpos($app["route"], '/collections') === 0)
        ], 5);

        // handle global search request
        $app->on("cockpit.globalsearch", function($search, $list) use($app){

            foreach ($app->db->find("common/collections") as $c) {
                if(stripos($c["name"], $search)!==false){
                    $list[] = [
                        "title" => '<i class="uk-icon-list"></i> '.$c["name"],
                        "url"   => $app->routeUrl('/collections/entries/'.$c["_id"])
                    ];
                }
            }
        });

    });

    $app->on("admin.dashboard.aside", function() use($app){

        if(!$app->module("auth")->hasaccess("Collections", ['manage.collections', 'manage.entries'])) return;

        $title       = $app("i18n")->get("Collections");
        $badge       = $app->db->getCollection("common/collections")->count();
        $collections = $app->db->find("common/collections", ["limit"=> 3, "sort"=>["created"=>-1] ])->toArray();

        $app->renderView("collections:views/dashboard.php with cockpit:views/layouts/dashboard.widget.php", compact('title', 'badge', 'collections'));
    });

    // acl
    $app("acl")->addResource("Collections", ['manage.collections', 'manage.entries']);

}