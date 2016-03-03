<?php

namespace Cockpit\Controller;

class RestAdmin extends \Cockpit\AuthController {


    public function index() {

        $keys = $this->app->module('cockpit')->loadApiKeys();

        return $this->render('cockpit:views/restadmin/index.php', compact('keys'));
    }


    public function save() {

        $data = $this->param('data', false);

        if (!$data) {
            return false;
        }

        return ['success' => $this->app->module('cockpit')->saveApiKeys($data)];
    }

}
