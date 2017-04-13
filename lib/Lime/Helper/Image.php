<?php

namespace Lime\Helper;

use claviska\SimpleImage;


class Image extends \Lime\Helper {

    public function take($imgpath) {

        $img = new Img($imgpath);

        return $img;
    }
}

class Img {

    protected $image;

    public function __construct($img) {

        $this->image = new SimpleImage($img);
    }

    public function negative() {
        $this->image->invert();
        return $this;
    }

    public function grayscale() {
        $this->image->desaturate();
        return $this;
    }

    public function base64data($format=null, $quality=100) {
        return $this->image->toDataUri($format, $quality);
    }

    public function show($format=null, $quality=100) {
        $this->image->output($format, $quality);
    }

    public function __call($method, $args) {
        call_user_func_array([$this->image, $method], $args);
        return $this;
    }
}
