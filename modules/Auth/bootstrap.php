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


if (COCKPIT_ADMIN) {

    // register controller
    $app->bindClass("Auth\\Controller\\Auth", 'auth');

    $app->on('auth.authenticate', function() use($app) {
        $app->reroute('/auth/login');
    });
}