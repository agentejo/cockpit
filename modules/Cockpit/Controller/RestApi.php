<?php
namespace Cockpit\Controller;

class RestApi extends \LimeExtra\Controller {

    public function authUser() {

        $response = ['error' => 'Authentication failed'];
        $data     = [ 'user' => $this->param('user'), 'password' => $this->param('password') ];

        if (!$data['user'] || !$data['password']) {
            return $response;
        }

        $user = $this->module('cockpit')->authenticate($data);

        if ($user) {
            $response = $user;
        }

        return $response;
    }

    
}
