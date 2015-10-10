<?php

namespace Lime\Helper;

use abeautifulsite\SimpleImage;

class Image extends \Lime\Helper {

	public function take($imgpath) {

        $img = new Img($imgpath);

        return $img;
	}
}

class Img {

    protected $imagine;
    protected $image;

    /**
     * @param $img
     */
    public function __construct($img) {

        $this->image = new SimpleImage($img);
    }

    /**
     * @return $this
     */
    public function negative() {
        $this->image->invert();
        return $this;
    }

    /**
     * @return $this
     */
    public function grayscale() {
        $this->image->desaturate();
        return $this;
    }

    /**
     * @return $this
     */
    public function sketch() {
        $this->image->sketch();
        return $this;
    }

    /**
     * @param $colorhex
     * @param int $opacity
     * @return $this
     */
    public function colorize($colorhex, $opacity=1) {
        $this->image->colorize($colorhex, $opacity);
        return $this;
    }

    /**
     * @return $this
     */
    public function flipHorizontally(){

        $this->image->flip('x');

        return $this;
    }

    /**
     * @return $this
     */
    public function flipVertically(){

        $this->image->flip('y');

        return $this;
    }

    /**
     * @param $overlay_file
     * @param string $position
     * @param int $opacity
     * @param int $x_offset
     * @param int $y_offset
     * @return $this
     */
    public function overlay($overlay_file, $position = 'center', $opacity = 1, $x_offset = 0, $y_offset = 0) {

        $this->image->overlay($overlay_file, $position , $opacity, $x_offset, $y_offset);

        return $this;
    }

    /**
     * @param $text
     * @param $font_file
     * @param int $font_size
     * @param string $color
     * @param string $position
     * @param int $x_offset
     * @param int $y_offset
     * @return $this
     * @throws \Exception
     */
    public function text($text, $font_file, $font_size = 12, $color = '#000000', $position = 'center', $x_offset = 0, $y_offset = 0) {

        $this->image->text($text, $font_file, $font_size, $color, $position, $x_offset, $y_offset);

        return $this;
    }

    /**
     * @param $width
     * @param $height
     * @return $this
     */
    public function thumbnail($width, $height) {

        $this->image->thumbnail($width, $height);

        return $this;
    }

    /**
     * @param $startX
     * @param $startY
     * @param $endX
     * @param $endY
     * @return $this
     */
    public function crop($startX, $startY, $endX, $endY) {

        $this->image->crop($startX, $startY, $endX, $endY);

        return $this;
    }

    /**
     * @param $width
     * @param $height
     * @return $this
     */
    public function resize($width, $height) {

        $this->image->resize($width, $height);

        return $this;
    }

    /**
     * @param $width
     * @param $height
     * @return $this
     */
    public function best_fit($width, $height) {

        $this->image->best_fit($width, $height);

        return $this;
    }

    /**
     * @param $angle
     * @return $this
     */
    public function rotate($angle) {

        $this->image->rotate($angle);

        return $this;
    }

    /**
     * @param null $format
     * @param int $quality
     * @return string
     * @throws \Exception
     */
    public function base64data($format=null, $quality=100) {
        return $this->image->output_base64($format, $quality);
    }

    /**
     * @param null $format
     * @param int $quality
     * @throws \Exception
     */
    public function show($format=null, $quality=100) {
        $this->image->output($format, $quality);
    }

    /**
     * @param $path
     * @param int $quality
     * @return $this
     * @throws \Exception
     */
    public function save($path, $quality=100) {
        $this->image->save($path, $quality);
        return $this;
    }
}