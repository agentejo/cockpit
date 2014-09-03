<?php

namespace Cockpit\Controller;

class RestApi extends \LimeExtra\Controller {

    public function call() {

        $module    = $this->param('module', null);
        $method    = $this->param('method', null);
        $arguments = $this->param('args', []);

        if (!$module || !$method) {
            return '{"error": true}';
        }

        $return = call_user_func_array([$this->app->module($module), $method], $arguments);

        return json_encode($return);
    }
}