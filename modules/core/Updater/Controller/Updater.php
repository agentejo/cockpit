<?php

namespace Updater\Controller;

class Updater extends \Cockpit\Controller {


    public function index(){

        return $this->render("updater:views/index.php");
    }

}