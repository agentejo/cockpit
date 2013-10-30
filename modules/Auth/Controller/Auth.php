<?php

namespace Auth\Controller;

class Auth extends \LimeExtra\Controller {


    public function check() {


        if($data = $this->param('auth')) {

            $user = $this->app->module('auth')->authenticate($data);

            if($user) {
                $this("session")->write('app.auth', $user);
            }

            if($this->req_is('ajax')) {
                return $user ? '{"success":1}' : '{"success":0}';
            } else {
                $this->reroute('/');
            }

        }

        return false;
    }


    public function login() {

        if(!$this->app->data->cockpit->accounts->count()) {
            $this->reroute($this->app->getSiteUrl().'/install');
        }

        return $this->render('cockpit:views/layouts/login.php');
    }

    public function logout() {

        $this("session")->delete('app.auth');

        if($this->req_is('ajax')) {
            return '{"logout":1}';
        } else {
            $this->reroute('/auth/login');
        }
    }
}