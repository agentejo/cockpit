<?php

// API


$this->module("galleries")->gallery = function($name) use($app) {

    $gallery = $app->data->common->galleries->findOne(["name"=>$name]);

    return $gallery ? $gallery["images"] : null;
};


if(!function_exists("gallery")) {
    function gallery($name) {
        return cockpit("galleries")->gallery($name);
    }
}


// ADMIN

if(COCKPIT_ADMIN) {


    $app->on("admin.init", function() use($app){

        if(!$app->module("auth")->hasaccess("galleries", "manage")) return;

        $app->bindClass("Galleries\\Controller\\Galleries", "galleries");
        $app->bindClass("Galleries\\Controller\\Api", "api/galleries");

        $app("admin")->menu("top", [
            "url"    => $app->routeUrl("/galleries"),
            "label"  => '<i class="uk-icon-picture-o"></i>',
            "title"  => $app("i18n")->get("Galleries"),
            "active" => (strpos($app["route"], '/galleries') === 0)
        ], 5);
    });

    $app->on("admin.dashboard", function() use($app){

        if(!$app->module("auth")->hasaccess("galleries","manage")) return;

        $title   = $app("i18n")->get("Galleries");
        $badge   = $app->data->common->galleries->count();
        $galleries = $app->data->common->galleries->find()->limit(3)->sort(["created"=>-1])->toArray();

        echo $app->view("galleries:views/dashboard.php with cockpit:views/layouts/dashboard.widget.php", compact('title', 'badge', 'galleries'));
    });


    // acl
    $app("acl")->addResource("Galleries", ['manage']);
}