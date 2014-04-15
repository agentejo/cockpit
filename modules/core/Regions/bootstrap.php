<?php

// API

$this->module("regions")->extend([

    "render" => function($name, $params = []) use($app) {

        $region = $app->db->findOne("common/regions", ["name"=>$name]);

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
    }
]);

// extend lexy parser
$app->renderer()->extend(function($content){

    $content = preg_replace('/(\s*)@region\((.+?)\)/', '$1<?php echo cockpit("regions")->render($2); ?>', $content);

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

//rest
$app->on("cockpit.rest.init", function($routes) {
    $routes["regions"] = 'Regions\\Controller\\RestApi';
});

// ADMIN

if(COCKPIT_ADMIN && !COCKPIT_REST) {


    $app->on("admin.init", function() use($app){

        if(!$app->module("auth")->hasaccess("Regions", ['create.regions', 'edit.regions'])) return;

        $app->bindClass("Regions\\Controller\\Regions", "regions");
        $app->bindClass("Regions\\Controller\\Api", "api/regions");

        $app("admin")->menu("top", [
            "url"    => $app->routeUrl("/regions"),
            "label"  => '<i class="uk-icon-th-large"></i>',
            "title"  => $app("i18n")->get("Regions"),
            "active" => (strpos($app["route"], '/regions') === 0)
        ], 5);

        // handle global search request
        $app->on("cockpit.globalsearch", function($search, $list) use($app){

            foreach ($app->db->find("common/regions") as $r) {
                if(stripos($r["name"], $search)!==false){
                    $list[] = [
                        "title" => '<i class="uk-icon-th-large"></i> '.$r["name"],
                        "url"   => $app->routeUrl('/regions/region/'.$r["_id"])
                    ];
                }
            }
        });
    });

    $app->on("admin.dashboard.aside", function() use($app){

        if(!$app->module("auth")->hasaccess("Regions", ['create.regions', 'edit.regions'])) return;

        $title   = $app("i18n")->get("Regions");
        $badge   = $app->db->getCollection("common/regions")->count();
        $regions = $app->db->find("common/regions", ["limit"=> 3, "sort"=>["created"=>-1] ])->toArray();

        echo $app->view("regions:views/dashboard.php with cockpit:views/layouts/dashboard.widget.php", compact('title', 'badge', 'regions'));
    });


    // acl
    $app("acl")->addResource("Regions", ['create.regions', 'edit.regions']);

}