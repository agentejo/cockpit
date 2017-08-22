<?php

namespace ColorThief\Image\Adapter;

class GDImageAdapter extends ImageAdapter
{
    /**
     * @inheritdoc
     */
    public function load($resource)
    {
        if (!is_resource($resource) || get_resource_type($resource) != 'gd') {
            throw new \InvalidArgumentException("Passed variable is not a valid GD resource");
        }

        parent::load($resource);
    }

    /**
     * @inheritdoc
     */
    public function loadBinaryString($data)
    {
        $this->resource = @imagecreatefromstring($data);
        if ($this->resource === false) {
            throw new \InvalidArgumentException("Passed binary string is empty or is not a valid image");
        }
    }

    /**
     * @inheritdoc
     */
    public function loadFile($file)
    {
        list(, , $type) = @getImageSize($file);

        switch ($type) {
            case IMAGETYPE_GIF:
                $resource = @imagecreatefromgif($file);
                break;

            case IMAGETYPE_JPEG:
                $resource = @imagecreatefromjpeg($file);
                break;

            case IMAGETYPE_PNG:
                $resource = @imagecreatefrompng($file);
                break;

            default:
                throw new \RuntimeException("Image '{$file}' is not readable or does not exists.");
                break;
        }

        if ($resource === false) {
            throw new \RuntimeException("Image '{$file}' is not readable or does not exists.");
        }

        $this->resource = $resource;
    }

    /**
     * @inheritdoc
     */
    public function destroy()
    {
        if ($this->resource) {
            imagedestroy($this->resource);
        }
        parent::destroy();
    }

    /**
     * @inheritdoc
     */
    public function getHeight()
    {
        return imagesy($this->resource);
    }

    /**
     * @inheritdoc
     */
    public function getWidth()
    {
        return imagesx($this->resource);
    }

    /**
     * @inheritdoc
     */
    public function getPixelColor($x, $y)
    {
        $rgba = imagecolorat($this->resource, $x, $y);
        $color = imagecolorsforindex($this->resource, $rgba);
        return (object)$color;
    }
}
