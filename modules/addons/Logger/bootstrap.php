<?php

//API
$this->module("logger")->extend([

    "logger" => function() use($app) {

        $logger = $app->db->getCollection("addons/logger");
    
        if($logger) {
            return $logger;
        }
    }
]);

if(!function_exists("logger")) {
    function logger() {
        return cockpit("logger")->logger();
    }
}

// ADMIN
if(COCKPIT_ADMIN && !COCKPIT_REST) {
    
    $app->on("admin.init", function() use($app){
      
       if(!$app->module("auth")->hasaccess("Addons", ['manage.logger'])) return;
        
        // bind routes
        $app->bindClass("Logger\\Controller\\Logger", "logger");
        
        // bind api
        $app->bindClass("Logger\\Controller\\Api", "api/logger");
        
        // menu item
        $app("admin")->menu("top", [
            "url"    => $app->routeUrl("/logger"),
            "label"  => '<i class="uk-icon-list-alt"></i>',
            "title"  => $app("i18n")->get("Logger"),
            "active" => (strpos($app["route"], '/logger') === 0)
        ], -1);
        
    });
  
  
      $app->on("admin.dashboard.aside", function() use($app){

        if(!$app->module("auth")->hasaccess("Addons", ['manage.logger'])) return;

        $title       = $app("i18n")->get("Logger");
        $badge       = $app->db->getCollection("addons/logger")->count();
        $collections = $app->db->find("addons/logger", ["limit"=> 3, "sort"=>["created"=>-1] ])->toArray();

        echo $app->view("logger:views/dashboard.php with cockpit:views/layouts/dashboard.widget.php", compact('title', 'badge', 'collections'));
    });

  
   $actions = ['manage.logger'];
  $app("acl")->addResource("Addons", $actions);
}