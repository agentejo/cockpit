<?php

// ACL
$app("acl")->addResource("Regions", ['create.regions', 'edit.regions', 'manage.region.fields']);


$app->on("admin.init", function() {

    if (!$this->module("auth")->hasaccess("Regions", ['create.regions', 'edit.regions'])) return;

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
            if (stripos($r["name"], $search)!==false){
                $list[] = [
                    "title" => '<i class="uk-icon-th-large"></i> '.$r["name"],
                    "url"   => $this->routeUrl('/regions/region/'.$r["_id"])
                ];
            }
        }
    });
});

$app->on("admin.dashboard.aside", function() {

    if (!$this->module("auth")->hasaccess("Regions", ['create.regions', 'edit.regions'])) return;

    $title   = $this("i18n")->get("Regions");
    $badge   = $this->db->getCollection("common/regions")->count();
    $regions = $this->db->find("common/regions", ["limit"=> 3, "sort"=>["created"=>-1] ])->toArray();

    $this->renderView("regions:views/dashboard.php with cockpit:views/layouts/dashboard.widget.php", compact('title', 'badge', 'regions'));
});


// register content fields
$app->on("cockpit.content.fields.sources", function() {

    echo $this->assets([
        'regions:assets/field.regionpicker.js',
    ], $this['cockpit/version']);

});
