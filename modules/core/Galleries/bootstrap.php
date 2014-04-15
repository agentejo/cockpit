<?php

// API

$this->module("galleries")->extend([

    "gallery" => function($name) use($app) {

        $gallery = $app->db->findOne("common/galleries", ["name"=>$name]);

        return $gallery ? $gallery["images"] : null;
    }
]);


if(!function_exists("gallery")) {
    function gallery($name) {
        return cockpit("galleries")->gallery($name);
    }
}


//rest
$app->on("cockpit.rest.init", function($routes) {
    $routes["galleries"] = 'Galleries\\Controller\\RestApi';
});


// ADMIN

if(COCKPIT_ADMIN && !COCKPIT_REST) {


    $app->on("admin.init", function() use($app){

        if(!$app->module("auth")->hasaccess("Galleries", ['create.gallery', 'edit.gallery'])) return;

        $app->bindClass("Galleries\\Controller\\Galleries", "galleries");
        $app->bindClass("Galleries\\Controller\\Api", "api/galleries");

        $app("admin")->menu("top", [
            "url"    => $app->routeUrl("/galleries"),
            "label"  => '<i class="uk-icon-picture-o"></i>',
            "title"  => $app("i18n")->get("Galleries"),
            "active" => (strpos($app["route"], '/galleries') === 0)
        ], 5);

        // handle global search request
        $app->on("cockpit.globalsearch", function($search, $list) use($app){

            foreach ($app->db->find("common/galleries") as $g) {
                if(stripos($g["name"], $search)!==false){
                    $list[] = [
                        "title" => '<i class="uk-icon-picture-o"></i> '.$g["name"],
                        "url"   => $app->routeUrl('/galleries/gallery/'.$g["_id"])
                    ];
                }
            }
        });
    });

    $app->on("admin.dashboard.aside", function() use($app){

        if(!$app->module("auth")->hasaccess("Galleries", ['create.gallery', 'edit.gallery'])) return;

        $title     = $app("i18n")->get("Galleries");
        $badge     = $app->db->getCollection("common/galleries")->count();
        $galleries = $app->db->find("common/galleries", ["limit"=> 3, "sort"=>["created"=>-1] ])->toArray();

        echo $app->view("galleries:views/dashboard.php with cockpit:views/layouts/dashboard.widget.php", compact('title', 'badge', 'galleries'));
    });


    // acl
    $app("acl")->addResource("Galleries", ['create.gallery', 'edit.gallery']);
}
