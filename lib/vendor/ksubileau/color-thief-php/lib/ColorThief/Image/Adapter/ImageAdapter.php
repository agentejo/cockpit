<?php

namespace ColorThief\Image\Adapter;

/**
 * Base adapter implementation to handle image manipulation.
 */
abstract class ImageAdapter implements IImageAdapter
{
    /**
     * The image resource handler.
     */
    protected $resource;

    /**
     * {@inheritdoc}
     */
    public function load($resource)
    {
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy()
    {
        $this->resource = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getResource()
    {
        return $this->resource;
    }
}
