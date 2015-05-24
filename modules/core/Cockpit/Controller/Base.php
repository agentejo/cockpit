<?php

namespace Cockpit\Controller;

class Base extends \Cockpit\AuthController {

    public function dashboard() {

        $stream = [];

        return $this->render('cockpit:views/base/dashboard.php', compact('stream'));
    }

    public function search() {

        $query = $this->app->param("search", false);
        $list  = new \ArrayObject([]);

        if ($query) {
            $this->app->trigger("cockpit.search", [$query, $list]);
        }

        return json_encode($list->getArrayCopy());
    }

    public function call($module, $method) {

        $args = (array)$this->param('args', []);
        $acl  = $this->param('acl', null);

        if (!$acl) {
            return false;
        }

        if (!$this->module('cockpit')->hasaccess($module, $acl)) {
            return false;
        }

        $return = call_user_func_array([$this->app->module($module), $method], $args);

        return '{"result":'.json_encode($return).'}';
    }
}
