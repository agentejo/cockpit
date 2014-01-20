<?php

// API

$this->module("regions")->render = function($name, $params = []) use($app) {

    $region = $app->data->common->regions->findOne(["name"=>$name]);

    if(!$region) {
        return null;
    }

    $renderer = $app->renderer();
    $fields   = [];

    if(isset($region["fields"]) && count($region["fields"])) {
        foreach ($region["fields"] as &$field) {
            $fields[$field["name"]] = $field["value"];
        }
    }

    $fields = array_merge($fields, $params);

    $app->trigger("region_before_render", [$name, $region["tpl"], $fields]);

    $output = $renderer->execute($region["tpl"], $fields);

    $app->trigger("region_after_render", [$name, $output]);

    return $output;
};

// extend lexy parser
$app->renderer()->extend(function($content){

    $content = preg_replace('/(\s*)@region\((.+?)\)/', '$1<?php cockpit("regions")->render($2); ?>', $content);

    return $content;
});

if(!function_exists("region")) {
    function region($name, $params = []) {
        echo cockpit("regions")->render($name, $params);
    }
}

if(!function_exists("get_region")) {
    function get_region($name, $params = []) {
        return cockpit("regions")->render($name, $params);
    }
}

// ADMIN

if(COCKPIT_ADMIN) {


    $app->on("admin.init", function() use($app){

        if(!$app->module("auth")->hasaccess("Regions","manage")) return;

        $app->bindClass("Regions\\Controller\\Regions", "regions");
        $app->bindClass("Regions\\Controller\\Api", "api/regions");

        $app("admin")->menu("top", [
            "url"    => $app->routeUrl("/regions"),
            "label"  => '<i class="uk-icon-th-large"></i>',
            "title"  => $app("i18n")->get("Regions"),
            "active" => (strpos($app["route"], '/regions') === 0)
        ], 5);
    });

    $app->on("admin.dashboard", function() use($app){

        if(!$app->module("auth")->hasaccess("Regions","manage")) return;

        $title   = $app("i18n")->get("Regions");
        $badge   = $app->data->common->regions->count();
        $regions = $app->data->common->regions->find()->limit(3)->sort(["created"=>-1])->toArray();

        echo $app->view("regions:views/dashboard.php with cockpit:views/layouts/dashboard.widget.php", compact('title', 'badge', 'regions'));
    });


    // acl
    $app("acl")->addResource("Regions", ['manage']);

}