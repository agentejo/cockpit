<?php

namespace League\ColorExtractor;

class Palette implements \Countable, \IteratorAggregate
{
    /** @var array */
    protected $colors;

    /**
     * @return int
     */
    public function count()
    {
        return count($this->colors);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->colors);
    }

    /**
     * @param int $color
     *
     * @return int
     */
    public function getColorCount($color)
    {
        return $this->colors[$color];
    }

    /**
     * @param int $limit = null
     *
     * @return array
     */
    public function getMostUsedColors($limit = null)
    {
        return array_slice($this->colors, 0, $limit, true);
    }

    /**
     * @param string   $filename
     * @param int|null $backgroundColor
     *
     * @return Palette
     */
    public static function fromFilename($filename, $backgroundColor = null)
    {
        $image = imagecreatefromstring(file_get_contents($filename));
        $palette = self::fromGD($image, $backgroundColor);
        imagedestroy($image);

        return $palette;
    }

    /**
     * @param resource $image
     * @param int|null $backgroundColor
     *
     * @return Palette
     *
     * @throws \InvalidArgumentException
     */
    public static function fromGD($image, $backgroundColor = null)
    {
        if (!is_resource($image) || get_resource_type($image) != 'gd') {
            throw new \InvalidArgumentException('Image must be a gd resource');
        }
        if ($backgroundColor !== null && (!is_numeric($backgroundColor) || $backgroundColor < 0 || $backgroundColor > 16777215)) {
            throw new \InvalidArgumentException(sprintf('"%s" does not represent a valid color', $backgroundColor));
        }

        $palette = new self();

        $areColorsIndexed = !imageistruecolor($image);
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        $palette->colors = [];

        $backgroundColorRed = ($backgroundColor >> 16) & 0xFF;
        $backgroundColorGreen = ($backgroundColor >> 8) & 0xFF;
        $backgroundColorBlue = $backgroundColor & 0xFF;

        for ($x = 0; $x < $imageWidth; ++$x) {
            for ($y = 0; $y < $imageHeight; ++$y) {
                $color = imagecolorat($image, $x, $y);
                if ($areColorsIndexed) {
                    $colorComponents = imagecolorsforindex($image, $color);
                    $color = ($colorComponents['alpha'] * 16777216) +
                             ($colorComponents['red'] * 65536) +
                             ($colorComponents['green'] * 256) +
                             ($colorComponents['blue']);
                }

                if ($alpha = $color >> 24) {
                    if ($backgroundColor === null) {
                        continue;
                    }

                    $alpha /= 127;
                    $color = (int) (($color >> 16 & 0xFF) * (1 - $alpha) + $backgroundColorRed * $alpha) * 65536 +
                             (int) (($color >> 8 & 0xFF) * (1 - $alpha) + $backgroundColorGreen * $alpha) * 256 +
                             (int) (($color & 0xFF) * (1 - $alpha) + $backgroundColorBlue * $alpha);
                }

                isset($palette->colors[$color]) ?
                    $palette->colors[$color] += 1 :
                    $palette->colors[$color] = 1;
            }
        }

        arsort($palette->colors);

        return $palette;
    }

    protected function __construct()
    {
        $this->colors = [];
    }
}
