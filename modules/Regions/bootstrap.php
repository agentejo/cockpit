<?php

// API

$this->module("regions")->render = function($name, $params = []) use($app) {

    $region = $app->data->common->regions->findOne(["name"=>$name]);

    if(!$region) {
        return null;
    }

    $renderer = new \Lexy();
    $fields   = [];

    if(isset($region["fields"]) && count($region["fields"])) {
        foreach ($region["fields"] as &$field) {
            $fields[$field["name"]] = $field["value"];
        }
    }

    $fields = array_merge($fields, $params);

    return $renderer->execute($region["tpl"], $fields);
};

if(!function_exists("region")) {
    function region($name, $params = []) {
        echo c("regions")->render($name, $params);
    }
}

// ADMIN

if(COCKPIT_ADMIN) {


    $app->bindClass("Regions\\Controller\\Regions", "regions");
    $app->bindClass("Regions\\Controller\\Api", "api/regions");


    $app->on("admin.init", function() use($app){

        $app("admin")->menu("top", [
            "url"   => $app->routeUrl("/regions"),
            "label" => '<i class="uk-icon-th-large"></i>',
            "title" => "Regions"
        ], 1);
    });

    $app->on("admin.dashboard", function() use($app){

        $title = "Regions";
        $badge = $app->data->common->regions->count();

        echo $app->view("regions:views/dashboard.php with cockpit:views/layouts/dashboard.widget.php", compact('title', 'badge'));
    });
}