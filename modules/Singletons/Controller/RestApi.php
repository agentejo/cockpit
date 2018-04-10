<?php
namespace Singletons\Controller;

class RestApi extends \LimeExtra\Controller {

    public function get($name = null) {

        if (!$name) {
            return false;
        }

        if ($user = $this->module('cockpit')->getUser()) {

            if (!$this->module('singletons')->hasaccess($name, 'data') && !$this->module('singletons')->hasaccess($name, 'form')) {
                return $this->stop('{"error": "Unauthorized"}', 401);
            }
        }

        $data= $this->module('singletons')->getData($name);

        if (!$data) {
            return false;
        }

        // workaround for now, may change later!
        if ($this->param('populate') && function_exists('cockpit_populate_collection')) {

            $fieldsFilter = [];

            if ($user) $fieldsFilter["user"] = $user;

            $_items = [$data];
            $_items = cockpit_populate_collection($_items, intval($this->param('populate')), 0, $fieldsFilter);
            $data = $_items[0];
        }

        return $data;
    }

    public function listSingletons($extended = false) {

        $user = $this->module('cockpit')->getUser();

        if ($user) {
            $singleton = $this->module('singletons')->getSingletonsInGroup($user['group']);
        } else {
            $singleton = $this->module('singletons')->singletons();
        }

        return $extended ? $singleton : array_keys($singleton);
    }

}
