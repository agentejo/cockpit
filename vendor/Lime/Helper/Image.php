<?php

namespace Lime\Helper;

use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\Color;

class Image extends \Lime\Helper {

	public function take($imgpath) {

        $img = new ImagineImage($imgpath);

        return $img;
	}
}

class ImagineImage {

    protected $imagine;
    protected $image;

    public function __construct($img) {

        if(class_exists("Imagick")) {
            $imagine = new \Imagine\Imagick\Imagine();
        } elseif(class_exists('Gmagick')) {
            $imagine = new \Imagine\Gmagick\Imagine();
        } else {
            $imagine = new \Imagine\Gd\Imagine();
        }

        $this->image   = $imagine->open($img);
        $this->imagine = $imagine;
    }

    public function negative() {
        $this->image->effects()->negative();
        return $this;
    }

    public function gamma($value) {
        $this->image->effects()->gamma($value);
        return $this;
    }

    public function grayscale() {
        $this->image->effects()->grayscale();
        return $this;
    }

    public function colorize($colorhexcode) {

        $color = new Color($colorhexcode);
        $this->image->effects()->colorize($color);

        return $this;
    }

    public function flipHorizontally(){

        $this->image->flipHorizontally();

        return $this;
    }

    public function flipVertically(){

        $this->image->flipVertically();

        return $this;
    }

    public function thumbnail($width, $height, $mode="inset") {

        $thumb = $this->image->thumbnail(new Box($width, $height), $mode);

        return $thumb;
    }

    public function crop($startX, $startY, $width, $height) {

        $this->image->crop(new Point($startX, $startY), new Box($width, $height));

        return $this;
    }

    public function resize($width, $height) {

        $this->image->resize(new Box($width, $height));

        return $this;
    }

    public function rotate($angle, $background=null) {

        if($background) {
            $background = new Color($background);
        }

        $this->image->rotate($angl, $backgrounde);

        return $this;
    }

    public function show($format) {
        $this->image->save($format);
    }


    public function save($path) {
        $this->image->save($path);
        return $this;
    }
}