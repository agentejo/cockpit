<?php

// ACL
$app("acl")->addResource("Datastore", ['manage.datastore']);



$app->on('admin.init', function() {

    if (!$this->module('auth')->hasaccess('Datastore', ['manage.datastore'])) return;

    // bind controllers
    $this->bindClass("Datastore\\Controller\\Datastore", "datastore");
    $this->bindClass("Datastore\\Controller\\Api", "api/datastore");

    // handle global search request
    $this->on("cockpit.globalsearch", function($search, $list) {

        foreach ($this->db->find("common/datastore") as $d) {

            if (stripos($d["name"], $search)!==false){
                $list[] = [
                    "title" => '<i class="uk-icon-database"></i> '.$d["name"],
                    "url"   => $this->routeUrl('/datastore/table/'.$d["_id"])
                ];
            }
        }
    });

});
