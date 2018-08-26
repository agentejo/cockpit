<?php

namespace Forms\Controller;

class RestApi extends \LimeExtra\Controller {

    public function submit($form) {

        $frm = $this->module('forms')->form($form);

        if (!$frm) {
            return false;
        }

        if ($data = $this->param('form', false)) {
            return $this->module('forms')->submit($form, $data);
        }

        return false;
    }
	
	public function export($form) {

        $form = $this->module('forms')->form($form);

        if (!$form) {
            return false;
        }

        $entries = $this->module('forms')->find($form['name']);

        return json_encode($entries, JSON_PRETTY_PRINT);
    }
}
