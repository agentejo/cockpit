<?php

namespace ColorThief\Image\Adapter;

/**
 * Basic interface for all image adapters.
 */
interface IImageAdapter
{
    /**
     * Loads an image from file.
     *
     * @param string $path
     */
    public function loadFile($path);

    /**
     * Loads an image from a binary string representation.
     *
     * @param string $data
     */
    public function loadBinaryString($data);

    /**
     * Loads an image resource.
     *
     * @param resource|object $resource
     */
    public function load($resource);

    /**
     * Destroys the image.
     */
    public function destroy();

    /**
     * Returns image height.
     *
     * @return int
     */
    public function getHeight();

    /**
     * Returns image width.
     *
     * @return int
     */
    public function getWidth();

    /**
     * Returns the color of the specified pixel.
     *
     * @param int $x
     * @param int $y
     *
     * @return object
     */
    public function getPixelColor($x, $y);

    /**
     * Get the raw resource.
     *
     * @return resource
     */
    public function getResource();
}
