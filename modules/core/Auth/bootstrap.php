<?php


$this->module("auth")->authenticate = function($data) use($app) {

    $data = array_merge([
        "user"     => "",
        "email"    => "",
        "group"    => "",
        "password" => ""
    ], $data);

    if (!$data["password"]) return false;

    $user = $app->data->cockpit->accounts->findOne([
            "user"     => $data["user"],
            "password" => $app->hash($data["password"]),
            "active"   => 1
    ]);

    if(count($user)) {

        $user = array_merge($data, (array)$user);

        return $user;
    }

    return false;
};


$this->module("auth")->hasaccess = function($resource, $action) use($app) {

    $user = $app("session")->read("app.auth");

    return isset($user["group"]) ? $app("acl")->hasaccess($user["group"], $resource, $action) : true;
};


if (COCKPIT_ADMIN) {

    // register controller
    $app->bindClass("Auth\\Controller\\Auth", 'auth');

    $app->on('auth.authenticate', function() use($app) {
        $app->reroute('/auth/login');
    });

    // init acl

    $app("acl")->addGroup("admin", true);

    if($user = $app("session")->read("app.auth")) {

        foreach ($app->memory->get("cockpit.acl.groups", []) as $group => $isadmin) {
            $app("acl")->addGroup($group, $isadmin);
        }

        foreach ($app->memory->get("cockpit.acl.rights", []) as $group => &$resources) {
            
            if(!$app("acl")->hasGroup($group)) continue;

            foreach ($resources as $resource => &$actions) {
                foreach ($actions as $action => &$value) {
                    if ($value) $app("acl")->allow($group, $resource, $action);
                }
            }
        }
    }
}