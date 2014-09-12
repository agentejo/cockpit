<?php

namespace Mediamanager\Controller;

class RestApi extends \LimeExtra\Controller {


    /*
        deprecated
    */
    public function thumbnail($image, $width = 50, $height = 50) {

        $image  = base64_decode($image);
        $imgurl = $this->module("mediamanager")->thumbnail($image, $width, $height);

        return $imgurl;
    }


    public function thumbnails() {

        return json_encode($this->module("mediamanager")->thumbnails([
            'images'  => $this->param('images', []),
            'width'   => $this->param('w', 50),
            'height'  => $this->param('h', false),
            'options' => $this->param('options', [])
        ]));
    }

}
