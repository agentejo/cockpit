<?php

namespace Regions\Controller;


class Admin extends \Cockpit\AuthController {


    public function index() {

        return $this->render('regions:views/index.php');
    }

    public function region($name = null) {

        $region = [ 'name'=>'', 'fields'=>[], 'template' => '', 'data' => null];

        if ($name) {

            $region = $this->module('regions')->region($name);

            if (!$region) {
                return false;
            }
        }

        return $this->render('regions:views/region.php', compact('region'));
    }

    public function form($name = null) {

        if ($name) {

            $region = $this->module('regions')->region($name);

            if (!$region) {
                return false;
            }

            return $this->render('regions:views/form.php', compact('region'));
        }

        return false;
    }
}
