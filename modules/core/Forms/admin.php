<?php

// ACL
$app("acl")->addResource("Forms", ['manage.forms', 'manage.entries']);

$app->on("admin.init", function() {

    if (!$this->module("auth")->hasaccess("Forms", ['manage.forms', 'manage.entries'])) return;

    $this->bindClass("Forms\\Controller\\Forms", "forms");
    $this->bindClass("Forms\\Controller\\Api", "api/forms");

    $this("admin")->menu("top", [
        "url"    => $this->routeUrl("/forms"),
        "label"  => '<i class="uk-icon-inbox"></i>',
        "title"  => $this("i18n")->get("Forms"),
        "active" => (strpos($this["route"], '/forms') === 0)
    ], 5);

    // handle global search request
    $this->on("cockpit.globalsearch", function($search, $list) {

        foreach ($this->db->find("common/forms") as $f) {
            if (stripos($f["name"], $search)!==false){
                $list[] = [
                    "title" => '<i class="uk-icon-inbox"></i> '.$f["name"],
                    "url"   => $this->routeUrl('/forms/form/'.$f["_id"])
                ];
            }
        }
    });
});

$app->on("admin.dashboard.aside", function() {

    if (!$this->module("auth")->hasaccess("Forms", ['manage.forms', 'manage.entries'])) return;

    $title = $this("i18n")->get("Forms");
    $badge = $this->db->getCollection("common/forms")->count();
    $forms = $this->db->find("common/forms", ["limit"=> 3, "sort"=>["created"=>-1] ])->toArray();

    $this->renderView("forms:views/dashboard.php with cockpit:views/layouts/dashboard.widget.php", compact('title', 'badge', 'forms'));
});
