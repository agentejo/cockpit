<?php

// extend lexy parser
$app->renderer->extend(function($content){

    $content = preg_replace('/(\s*)@hasaccess\?\((.+?)\)/', '$1<?php if ($app->module("auth")->hasaccess($2)) { ?>', $content);

    return $content;
});

// register controller

$app->bindClass("Auth\\Controller\\Auth", 'auth');
$app->bindClass("Auth\\Controller\\Accounts", "accounts");

// init acl

$app["cockpit.acl.groups.settings"] = $app->db->getKey("cockpit/settings", "cockpit.acl.groups.settings", new \ArrayObject([]));

$app("acl")->addGroup("admin", true);

if ($user = $app->module("auth")->getUser()) {

    foreach ($app->db->getKey("cockpit/settings", "cockpit.acl.groups", []) as $group => $isadmin) {
        $app("acl")->addGroup($group, $isadmin);
    }

    foreach ($app->db->getKey("cockpit/settings", "cockpit.acl.rights", []) as $group => $resources) {

        if (!$app("acl")->hasGroup($group)) continue;

        foreach ($resources as $resource => $actions) {
            foreach ($actions as $action => $value) {
                if ($value) $app("acl")->allow($group, $resource, $action);
            }
        }
    }
}
