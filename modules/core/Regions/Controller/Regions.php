<?php

namespace Regions\Controller;

class Regions extends \Cockpit\Controller {


	public function index(){
    $control = $this->app->module("auth")->hasaccess("Regions","control");
		return $this->render("regions:views/index.php", compact('control'));
	}


    public function region($id=null){
        return $this->render("regions:views/region.php", compact('id'));
    }

}
