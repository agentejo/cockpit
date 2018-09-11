<?php

namespace Forms\Controller;

class RestApi extends \LimeExtra\Controller {

    public function submit($form) {

        $frm = $this->module('forms')->form($form);

        if (!$frm) {
            return false;
        }

        if ($data = $this->param('form', false)) {

            $options = [];

            if ($this->param('__mailsubject')) {
                $options['subject'] = $this->param('__mailsubject');
            }

            return $this->module('forms')->submit($form, $data, $options);
        }

        return false;
    }
}
