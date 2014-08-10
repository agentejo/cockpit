<?php

namespace Mediamanager\Controller;

class RestApi extends \LimeExtra\Controller {


    /*
        deprecated
    */
    public function thumbnail($image, $width = 50, $height = 50) {

        $image = base64_decode($image);

        $imgurl = $this->module("mediamanager")->thumbnail($image, $width, $height);

        return $imgurl;
    }


    public function thumbnails() {

        $images  = $this->param('images', []);
        $width   = $this->param('w', 50);
        $height  = $this->param('h', false);

        $options = [
            "quality"     => $this->param('q', 100),
            "base64"      => $this->param('base64', false),
            "mode"        => $this->param('m', 'crop')
        ];

        $urls = [];

        foreach ($images as $image) {
            $urls[$image] = $this->module("mediamanager")->thumbnail($image, $width, $height, $options);
        }

        return json_encode($urls);
    }

}