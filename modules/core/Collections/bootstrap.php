<?php

// API

$this->module("collections")->collection = function($name) use($app) {

    $entriesdb  = null;
    $collection = $app->data->common->collections->findOne(["name"=>$name]);

    if($collection) {
        $collection = "collection".$collection["_id"];
        $entriesdb  = $app->data->collections->{$collection};
    }

    return $entriesdb;
};

if(!function_exists("collection")) {
    function collection($name) {
        return cockpit("collections")->collection($name);
    }
}

// ADMIN

if(COCKPIT_ADMIN) {


    // bind controllers
    $app->bindClass("Collections\\Controller\\Collections", "collections");
    $app->bindClass("Collections\\Controller\\Api", "api/collections");


    $app->on("admin.init", function() use($app){

        $app("admin")->menu("top", [
            "url"    => $app->routeUrl("/collections"),
            "label"  => '<i class="uk-icon-list"></i>',
            "title"  => "Collections",
            "active" => (strpos($app["route"], '/collections') === 0)
        ], 1);
    });

    $app->on("admin.dashboard", function() use($app){

        $title       = "Collections";
        $badge       = $app->data->common->collections->count();
        $collections = $app->data->common->collections->find()->limit(3)->sort(["created"=>-1])->toArray();

        echo $app->view("collections:views/dashboard.php with cockpit:views/layouts/dashboard.widget.php", compact('title', 'badge', 'collections'));
    });

}