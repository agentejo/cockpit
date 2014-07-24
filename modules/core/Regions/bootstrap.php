<?php

// API

$this->module("regions")->extend([

    "render" => function($name, $params = []) use($app) {

        $region = $app->db->findOne("common/regions", ["name"=>$name]);

        if(!$region) {
            return null;
        }

        $renderer = $app->renderer;
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
$app->renderer->extend(function($content){

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


    $app->on("admin.init", function() {

        if(!$this->module("auth")->hasaccess("Regions", ['create.regions', 'edit.regions'])) return;

        $this->bindClass("Regions\\Controller\\Regions", "regions");
        $this->bindClass("Regions\\Controller\\Api", "api/regions");

        $this("admin")->menu("top", [
            "url"    => $this->routeUrl("/regions"),
            "label"  => '<i class="uk-icon-th-large"></i>',
            "title"  => $this("i18n")->get("Regions"),
            "active" => (strpos($this["route"], '/regions') === 0)
        ], 5);

        // handle global search request
        $this->on("cockpit.globalsearch", function($search, $list) {

            foreach ($this->db->find("common/regions") as $r) {
                if(stripos($r["name"], $search)!==false){
                    $list[] = [
                        "title" => '<i class="uk-icon-th-large"></i> '.$r["name"],
                        "url"   => $this->routeUrl('/regions/region/'.$r["_id"])
                    ];
                }
            }
        });
    });

    $app->on("admin.dashboard.aside", function() {

        if(!$this->module("auth")->hasaccess("Regions", ['create.regions', 'edit.regions'])) return;

        $title   = $this("i18n")->get("Regions");
        $badge   = $this->db->getCollection("common/regions")->count();
        $regions = $this->db->find("common/regions", ["limit"=> 3, "sort"=>["created"=>-1] ])->toArray();

        $this->renderView("regions:views/dashboard.php with cockpit:views/layouts/dashboard.widget.php", compact('title', 'badge', 'regions'));
    });


    // acl
    $app("acl")->addResource("Regions", ['create.regions', 'edit.regions']);

}