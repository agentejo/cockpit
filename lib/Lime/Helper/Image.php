<?php

namespace Lime\Helper;


class Image extends \Lime\Helper {

	public function take($imgpath) {

        $img = new Img($imgpath);

        return $img;
	}
}

class Img {

    protected $imagine;
    protected $image;

    public function __construct($img) {

        $this->image = new \SimpleImage($img);
    }

    public function negative() {
        $this->image->invert();
        return $this;
    }

    public function grayscale() {
        $this->image->desaturate();
        return $this;
    }

    public function sketch() {
        $this->image->sketch();
        return $this;
    }

    public function colorize($colorhex, $opacity=1) {
        $this->image->colorize($colorhex, $opacity);
        return $this;
    }

    public function flipHorizontally(){

        $this->image->flip('x');

        return $this;
    }

    public function flipVertically(){

        $this->image->flip('y');

        return $this;
    }

    public function overlay($overlay_file, $position = 'center', $opacity = 1, $x_offset = 0, $y_offset = 0) {

        $this->image->overlay($overlay_file, $position , $opacity, $x_offset, $y_offset);

        return $this;
    }

    public function text($text, $font_file, $font_size = 12, $color = '#000000', $position = 'center', $x_offset = 0, $y_offset = 0) {

        $this->image->text($text, $font_file, $font_size, $color, $position, $x_offset, $y_offset);

        return $this;
    }

    public function thumbnail($width, $height) {

        $this->image->thumbnail($width, $height);

        return $this;
    }

    public function crop($startX, $startY, $endX, $endY) {

        $this->image->crop($startX, $startY, $endX, $endY);

        return $this;
    }

    public function resize($width, $height) {

        $this->image->resize($width, $height);

        return $this;
    }

    public function best_fit($width, $height) {

        $this->image->best_fit($width, $height);

        return $this;
    }

    public function rotate($angle) {

        $this->image->rotate($angle);

        return $this;
    }

    public function base64data($format=null, $quality=100) {
        return $this->image->output_base64($format, $quality);
    }

    public function show($format=null, $quality=100) {
        $this->image->output($format, $quality);
    }


    public function save($path, $quality=100) {
        $this->image->save($path, $quality);
        return $this;
    }
}