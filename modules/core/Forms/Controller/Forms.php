<?php

namespace Forms\Controller;

class Forms extends \Cockpit\Controller {

	public function index(){
        $control = $this->app->module("auth")->hasaccess("Forms","control");
        return $this->render("forms:views/index.php", compact('control'));
    }

    public function form($id = null) {

        return $this->render("forms:views/form.php", compact('id'));
    }

    public function entries($id) {

        $form = $this->app->db->findOne("common/forms", ["_id" => $id]);

        if(!$form) {
            return false;
        }

        $count = $this->app->module("forms")->collectionById($form["_id"])->count();

        $form["count"] = $count;

        $control = $this->app->module("auth")->hasaccess("Forms","control");

        return $this->render("forms:views/entries.php", compact('id', 'form', 'count', 'control'));
    }
}
