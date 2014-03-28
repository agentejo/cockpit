<?php

// API

$this->module("auth")->extend([

    "authenticate" => function($data) use($app) {

        $data = array_merge([
            "user"     => "",
            "email"    => "",
            "group"    => "",
            "password" => ""
        ], $data);

        if (!$data["password"]) return false;

        $user = $app->db->findOne("cockpit/accounts", [
            "user"     => $data["user"],
            "password" => $app->hash($data["password"]),
            "active"   => 1
        ]);

        if(count($user)) {

            $user = array_merge($data, (array)$user);

            unset($user["password"]);

            return $user;
        }

        return false;
    },

    "setUser" => function($user) use($app) {
        $app("session")->write('cockpit.app.auth', $user);
    },

    "getUser" => function() use($app) {
        return $app("session")->read('cockpit.app.auth', null);
    },

    "logout" => function() use($app) {
        $app("session")->delete('cockpit.app.auth');
    },

    "hasaccess" => function($resource, $action) use($app) {

        $user = $app("session")->read("cockpit.app.auth");

        if(isset($user["group"])) {

            if($user["group"]=='admin') return true;
            if($app("acl")->hasaccess($user["group"], $resource, $action)) return true;
        }

        return false;
    },

    "getGroupSetting" => function($setting, $default = null) use($app) {

        if($user = $app("session")->read("cockpit.app.auth", null)) {
            if(isset($user["group"])) {

                $settings = $app["cockpit.acl.groups.settings"];

                return isset($settings[$user["group"]][$setting]) ? $settings[$user["group"]][$setting] : $default;
            }
        }

        return $default;
    }
]);


if (COCKPIT_ADMIN) {

    // extend lexy parser
    $app->renderer()->extend(function($content){

        $content = preg_replace('/(\s*)@hasaccess\?\((.+?)\)/', '$1<?php if($app->module("auth")->hasaccess($2)) { ?>', $content);

        return $content;
    });

    // register controller

    $app->bindClass("Auth\\Controller\\Auth", 'auth');
    $app->bindClass("Auth\\Controller\\Accounts", "accounts");

    // init acl

    $app["cockpit.acl.groups.settings"] = $app->memory->get("cockpit.acl.groups.settings", new \ArrayObject([]));

    $app("acl")->addGroup("admin", true);

    if($user = $app->module("auth")->getUser()) {

        foreach ($app->memory->get("cockpit.acl.groups", []) as $group => $isadmin) {
            $app("acl")->addGroup($group, $isadmin);
        }

        foreach ($app->memory->get("cockpit.acl.rights", []) as $group => $resources) {

            if (!$app("acl")->hasGroup($group)) continue;

            foreach ($resources as $resource => $actions) {
                foreach ($actions as $action => $value) {
                    if ($value) $app("acl")->allow($group, $resource, $action);
                }
            }
        }
    }
}