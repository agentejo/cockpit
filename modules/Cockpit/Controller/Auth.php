<?php

namespace Cockpit\Controller;

class Auth extends \LimeExtra\Controller {


    public function check() {

        if ($data = $this->param('auth')) {

            $user = $this->module('cockpit')->authenticate($data);

            if ($user && !$this->module("cockpit")->hasaccess('cockpit', 'backend', @$user['group'])) {
                $user = null;
            }

            if ($user) {
                $this->app->trigger("cockpit.account.login", [&$user]);
                $this->module('cockpit')->setUser($user);
            }

            if ($this->req_is('ajax')) {
                return $user ? json_encode(["success" => true, "user" => $user, "avatar"=> md5($user["email"])]) : '{"success": false}';
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

        $this->app->trigger("cockpit.account.logout", [$this->app->module('cockpit')->getUser()]);

        $this->module('cockpit')->logout();

        if ($this->req_is('ajax')) {
            return '{"logout":1}';
        } else {
            $this->reroute('/auth/login');
        }
    }
}
