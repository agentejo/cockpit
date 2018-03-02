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

        if ($user = $this->module('cockpit')->getUser()) {

            if (!$this->module('regions')->hasaccess($name, 'data') && !$this->module('regions')->hasaccess($name, 'form')) {
                return $this->stop('{"error": "Unauthorized"}', 401);
            }
        }

        $region = $this->module('regions')->region($name);

        if (!$region) {
            return false;
        }

        // workaround for now, may change later!
        if ($this->param('populate') && isset($region['data']) && function_exists('cockpit_populate_collection')) {

            $fieldsFilter = [];

            if ($user) $fieldsFilter["user"] = $user;

            $_items = [$region['data']];
            $_items = cockpit_populate_collection($_items, intval($this->param('populate')), 0, $fieldsFilter);
            $region['data'] = $_items[0];
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
