<?php

namespace ColorThief\Image\Adapter;

use Gmagick;

class GmagickImageAdapter extends ImageAdapter
{
    /**
     * @inheritdoc
     */
    public function load($resource)
    {
        if (!($resource instanceof Gmagick)) {
            throw new \InvalidArgumentException("Passed variable is not an instance of Gmagick");
        }

        parent::load($resource);
    }

    /**
     * @inheritdoc
     */
    public function loadBinaryString($data)
    {
        $this->resource = new Gmagick();
        try {
            $this->resource->readImageBlob($data);
        } catch (\GmagickException $e) {
            throw new \InvalidArgumentException("Passed binary string is empty or is not a valid image", 0, $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function loadFile($file)
    {
        // GMagick doesn't support HTTPS URL directly, so we download the image with file_get_contents first
        // and then we passed the binary string to GmagickImageAdapter::loadBinaryString().
        if (filter_var($file, FILTER_VALIDATE_URL)) {
            $image = @file_get_contents($file);
            if ($image === false) {
                throw new \RuntimeException("Image '" . $file . "' is not readable or does not exists.", 0);
            }
            return $this->loadBinaryString($image);
        }

        $this->resource = null;
        try {
            $this->resource = new Gmagick($file);
        } catch (\GmagickException $e) {
            throw new \RuntimeException("Image '" . $file . "' is not readable or does not exists.", 0, $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function destroy()
    {
        if ($this->resource) {
            $this->resource->clear();
            $this->resource->destroy();
        }
        parent::destroy();
    }

    /**
     * @inheritdoc
     */
    public function getHeight()
    {
        return $this->resource->getimageheight();
    }

    /**
     * @inheritdoc
     */
    public function getWidth()
    {
        return $this->resource->getimagewidth();
    }

    /**
     * @inheritdoc
     */
    public function getPixelColor($x, $y)
    {
        $cropped = clone $this->resource;    // No need to modify the original object.
        $histogram = $cropped->cropImage(1, 1, $x, $y)->getImageHistogram();
        $pixel = array_shift($histogram);

        // Un-normalized values don't give a full range 0-1 alpha channel
        // So we ask for normalized values, and then we un-normalize it ourselves.
        $colorArray = $pixel->getColor(true, true);
        $color = new \stdClass();
        $color->red = (int)round($colorArray['r'] * 255);
        $color->green = (int)round($colorArray['g'] * 255);
        $color->blue = (int)round($colorArray['b'] * 255);
        $color->alpha = (int)round($pixel->getcolorvalue(\Gmagick::COLOR_OPACITY) * 127);

        return $color;
    }
}
