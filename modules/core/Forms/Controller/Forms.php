<?php

namespace Forms\Controller;

class Forms extends \Cockpit\Controller {

	public function index(){
        return $this->render("forms:views/index.php");
    }

    public function form($id = null) {

        return $this->render("forms:views/form.php", compact('id'));
    }

    public function entries($id) {

        $form = $this->app->data->common->forms->findOne(["_id" => $id]);

        if(!$form) {
            return false;
        }

        $col   = "form".$form["_id"];
        $count = $this->app->data->forms->{$col}->count();

        $form["count"] = $count;

        return $this->render("forms:views/entries.php", compact('id', 'form', 'count'));
    }
}