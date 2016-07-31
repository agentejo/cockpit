<?php

namespace Cockpit\Controller;

class Webhooks extends \Cockpit\AuthController {

    public function index() {
        return $this->render('cockpit:views/webhooks/index.php');
    }

    public function hook() {
        return $this->render('cockpit:views/webhooks/index.php');
    }
}
