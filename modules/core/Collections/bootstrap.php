<?php

// API

$this->module("collections")->extend([

    "collection" => function($name) use($app) {

        $entriesdb  = null;
        $collection = $app->getCollection("common/collections")->findOne(["name"=>$name]);

        if($collection) {
            $collection = "collection".$collection["_id"];
            $entriesdb  = $app->getCollection("collections/{$collection}");
        }

        return $entriesdb;
    },

    "collectionById" => function($colid) use($app) {
        
        $collection = "collection{$colid}";

        return $app->getCollection("collections/{$collection}");

    }
]);

if(!function_exists("collection")) {
    function collection($name) {
        return cockpit("collections")->collection($name);
    }
}

//rest
$app->on("cockpit.rest.init", function($routes) {
    $routes["collections"] = 'Collections\\Controller\\RestApi';
});

// ADMIN

if(COCKPIT_ADMIN) {


    $app->on("admin.init", function() use($app){

        if(!$app->module("auth")->hasaccess("Collections","manage")) return;

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

            foreach ($app->getCollection("common/collections")->find()->toArray() as $c) {
                if(stripos($c["name"], $search)!==false){
                    $list[] = [
                        "title" => '<i class="uk-icon-list"></i> '.$c["name"],
                        "url"   => $app->routeUrl('/collections/collection/'.$c["_id"])
                    ];
                }
            }
        });

    });

    $app->on("admin.dashboard", function() use($app){

        if(!$app->module("auth")->hasaccess("Collections","manage")) return;

        $title       = $app("i18n")->get("Collections");
        $badge       = $app->getCollection("common/collections")->count();
        $collections = $app->getCollection("common/collections")->find()->limit(3)->sort(["created"=>-1])->toArray();

        echo $app->view("collections:views/dashboard.php with cockpit:views/layouts/dashboard.widget.php", compact('title', 'badge', 'collections'));
    });

    // acl
    $app("acl")->addResource("Collections", ['manage']);

}