<?php

namespace Regions\Controller;

class RestApi extends \LimeExtra\Controller {

    public function get($name = null) {

        if (!$name) {
            return false;
        }

        $content = $this->module("regions")->render($name);

        return is_null($content) ? false:$content;
    }

}