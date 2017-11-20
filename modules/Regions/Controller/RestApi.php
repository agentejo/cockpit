<?php
namespace Regions\Controller;

class RestApi extends \LimeExtra\Controller {

    public function get($name = null) {

        if (!$name) {
            return false;
        }

        if ($this->module('cockpit')->getUser()) {

            if (!$this->module('regions')->hasaccess($name, 'render') && !$this->module('regions')->hasaccess($name, 'form')) {
                return $this->stop('{"error": "Unauthorized"}', 401);
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

        if ($this->module('cockpit')->getUser()) {

            if (!$this->module('regions')->hasaccess($name, 'data') && !$this->module('regions')->hasaccess($name, 'form')) {
                return $this->stop('{"error": "Unauthorized"}', 401);
            }
        }

        $region = $this->module('regions')->region($name);

        if (!$region) {
            return false;
        }

        return isset($region['data']) ? $region['data'] : [];
    }

    public function listRegions($extended = false) {

        $user = $this->module('cockpit')->getUser();

        if ($user) {
            $regions = $this->module('regions')->getRegionsInGroup($user['group']);
        } else {
            $regions = $this->module('regions')->regions();
        }

        return $extended ? $regions : array_keys($regions);
    }

}
