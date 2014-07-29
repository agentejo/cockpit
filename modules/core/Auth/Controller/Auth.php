<?php

namespace Auth\Controller;

class Auth extends \LimeExtra\Controller {


    public function check() {


        if ($data = $this->param('auth')) {

            $user = $this->module('auth')->authenticate($data);

            if ($user) {

                $this->module("auth")->setUser($user);

                // log to history
                $this->helper("history")->log([
                    "msg"  => "%s logged in",
                    "args" => [$user["name"] ? $user["name"]:$user["user"]],
                    "mod"  => "auth"
                ]);
            }

            if ($this->req_is('ajax')) {
                return $user ? json_encode(["success" => true, "user" => $user, "avatar"=> md5($user["email"])]) : '{"success":0}';
            } else {
                $this->reroute('/');
            }
        }

        return false;
    }


    public function login() {

        return $this->render('cockpit:views/layouts/login.php');
    }

    public function logout() {

        $this->module('auth')->logout();

        if ($this->req_is('ajax')) {
            return '{"logout":1}';
        } else {
            $this->reroute('/auth/login');
        }
    }
}