<?php

namespace Datastore\Controller;


class Datastore extends \Cockpit\Controller {

	public function index(){

        return $this->render("datastore:views/index.php");
	}

    public function table($id = null){

        return $this->render("datastore:views/table.php", compact('id'));
    }


    public function entries($tableId){

        $entries = [];


        return json_encode($entries);
    }
}