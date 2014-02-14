<?php

namespace Regions\Controller;

class Regions extends \Cockpit\Controller {


	public function index(){
		return $this->render("regions:views/index.php");
	}


    public function region($id=null){

        if(!$id && !$this->app->module("auth")->hasaccess("Regions", 'create.regions')) {
            return false;
        }

        return $this->render("regions:views/region.php", compact('id'));
    }

}