<?php
namespace Regions\Controller;

class RestApi extends \LimeExtra\Controller {

    public function get($name = null) {

        if (!$name) {
            return false;
        }

        if ($this->module('cockpit')->getUser()) {
            if (!$this->module('regions')->hasaccess($name, 'form')) {
                return $this->stop(401);
            }
        }

        $params  = $this->param("params", []);
        $content = $this->module("regions")->render($name, $params);

        return is_null($content) ? false : $content;
    }

    public function data($name = null) {

        if (!$name) {
            return false;
        }

        $region = $this->module('regions')->region($name);

        if ($this->module('cockpit')->getUser()) {
            if (!$this->module('regions')->hasaccess($name, 'form')) {
                return $this->stop(401);
            }
        }

        if (!$region) {
            return false;
        }

        return isset($region['data']) ? $region['data'] : [];
    }

}
