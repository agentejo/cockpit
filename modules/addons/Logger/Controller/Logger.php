<?php

namespace Logger\Controller;

class Logger extends \Cockpit\Controller {

    public function index() {
        return $this->render("logger:views/index.php");
    }
    
}
