<?php
namespace Forms\Controller;

class RestApi extends \LimeExtra\Controller {

    public function submit($form) {

        $frm = $this->module('forms')->form($form);

        if (!$frm) {
            return false;
        }

        if ($data = $this->param('form', false)) {
            return json_encode($this->module('forms')->submit($formn, $data));
        }

        return false;
    }
}
