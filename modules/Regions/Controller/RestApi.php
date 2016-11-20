<?php
namespace Regions\Controller;

class RestApi extends \LimeExtra\Controller {

    public function get($name = null) {

        if (!$name) {
            return false;
        }

        $params  = $this->param("params", []);
        $content = $this->module("regions")->render($name, $params);

        return is_null($content) ? false : $content;
    }

    public function data($name = null) {

        if (!$name) {
            return false;
        }

        $region = $this->region($name);

        if (!$region) {
            return false;
        }

        return isset($region['data']) ? $region['data'] : [];
    }

}
