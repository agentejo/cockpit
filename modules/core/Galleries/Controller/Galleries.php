<?php

namespace Galleries\Controller;

class Galleries extends \Cockpit\Controller {


    public function index() {
        $control = $this->app->module("auth")->hasaccess("Galleries","control");
        return $this->render("galleries:views/index.php", compact('control'));
    }


    public function gallery($id=null){
        return $this->render("galleries:views/gallery.php", compact('id'));
    }

}
