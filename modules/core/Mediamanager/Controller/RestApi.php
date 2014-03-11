<?php

namespace Mediamanager\Controller;

class RestApi extends \LimeExtra\Controller {

    public function thumbnail($image, $width = 50, $height = 50) {

        $image = base64_decode($image);

        $imgurl = $this->module("mediamanager")->thumbnail($image, $width, $height);

        return $imgurl;
    }

}