<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LimeExtra\Helper;

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
        $this->image->toScreen($format, $quality);
    }

    public function blur($passes = 1, $type = 'gaussian') {
        return $this->image->blur($type, $passes);
    }

    public function thumbnail($width, $height, $anchor = 'center') {


        if (\preg_match('/\d \d/', $anchor)) {

            // Determine aspect ratios
            $currentRatio = $this->image->getHeight() / $this->image->getWidth();
            $targetRatio = $height / $width;

            // Fit to height/width
            if ($targetRatio > $currentRatio) {
              $this->image->resize(null, $height);
            } else {
              $this->image->resize($width, null);
            }

            $anchor = \explode(' ', $anchor);

            $x1 = \floor(($this->image->getWidth() * $anchor[0]) - ($width * $anchor[0]));
            $x2 = $width + $x1;
            $y1 = \floor(($this->image->getHeight() * $anchor[1]) - ($height * $anchor[1]));
            $y2 = $height + $y1;

            return $this->image->crop($x1, $y1, $x2, $y2);
        }

        return $this->image->thumbnail($width, $height, $anchor);
    }

    public function __call($method, $args) {

        $ret = \call_user_func_array([$this->image, $method], $args);

        if ($ret !== $this->image) {
            return $ret;
        }

        return $this;
    }
}
