<?php

namespace Galleries\Controller;

class RestApi extends \LimeExtra\Controller {

    public function get($name = null) {

        if (!$name) {
            return false;
        }

        $content = $this->module("galleries")->gallery($name);

        return json_encode(is_null($content) ? false:$content);
    }

}