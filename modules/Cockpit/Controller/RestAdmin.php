<?php

namespace Cockpit\Controller;

class RestAdmin extends \Cockpit\AuthController {

    public function __construct($app) {

        parent::__construct($app);

        if (!$this->module('cockpit')->hasaccess('cockpit', 'rest')) {
            return $this->helper('admin')->denyRequest();
        }
    }


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
