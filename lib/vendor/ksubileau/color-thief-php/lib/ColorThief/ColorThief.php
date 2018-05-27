<?php

/*
 * Color Thief PHP
 *
 * Grabs the dominant color or a representative color palette from an image.
 *
 * This class requires the GD library to be installed on the server.
 *
 * It's a PHP port of the Color Thief Javascript library
 * (http://github.com/lokesh/color-thief), using the MMCQ
 * (modified median cut quantization) algorithm from
 * the Leptonica library (http://www.leptonica.com/).
 *
 * by Kevin Subileau - http://www.kevinsubileau.fr
 * Based on the work done by Lokesh Dhakar - http://www.lokeshdhakar.com
 * and Nick Rabinowitz
 *
 * License
 * -------
 * Creative Commons Attribution 2.5 License:
 * http://creativecommons.org/licenses/by/2.5/
 *
 * Thanks
 * ------
 * Lokesh Dhakar - For creating the original project.
 * Nick Rabinowitz - For creating quantize.js.
 *
 */

namespace ColorThief;

use SplFixedArray;
use ColorThief\Image\ImageLoader;

class ColorThief
{
    const SIGBITS = 5;
    const RSHIFT = 3;
    const MAX_ITERATIONS = 1000;
    const FRACT_BY_POPULATIONS = 0.75;
    const THRESHOLD_ALPHA = 62;
    const THRESHOLD_WHITE = 250;

    /**
     * Get reduced-space color index for a pixel
     *
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int $sigBits
     * @return int
     */
    public static function getColorIndex($red, $green, $blue, $sigBits = self::SIGBITS)
    {
        return ($red << (2 * $sigBits)) + ($green << $sigBits) + $blue;
    }

    /**
     * Get red, green and blue components from reduced-space color index for a pixel
     *
     * @param int $index
     * @param int $rightShift
     * @param int $sigBits
     * @return array
     */
    public static function getColorsFromIndex($index, $rightShift = self::RSHIFT, $sigBits = 8)
    {
        $mask = (1 << $sigBits) - 1;
        $red = (($index >> (2 * $sigBits)) & $mask) >> $rightShift;
        $green = (($index >> $sigBits) & $mask) >> $rightShift;
        $blue = ($index & $mask) >> $rightShift;
        return array($red, $green, $blue);
    }

    /**
     * Natural sorting
     *
     * @param int $a
     * @param int $b
     * @return int
     */
    public static function naturalOrder($a, $b)
    {
        return ($a < $b) ? -1 : (($a > $b) ? 1 : 0);
    }

    /**
     * Use the median cut algorithm to cluster similar colors.
     *
     * @bug Function does not always return the requested amount of colors. It can be +/- 2.
     *
     * @param mixed      $sourceImage   Path/URL to the image, GD resource, Imagick instance, or image as binary string
     * @param int        $quality       1 is the highest quality. There is a trade-off between quality and speed.
     *                                  The bigger the number, the faster the palette generation but the greater the
     *                                  likelihood that colors will be missed.
     * @param array|null $area[x,y,w,h] It allows you to specify a rectangular area in the image in order to get
     *                                  colors only for this area. It needs to be an associative array with the
     *                                  following keys:
     *                                  $area['x']: The x-coordinate of the top left corner of the area. Default to 0.
     *                                  $area['y']: The y-coordinate of the top left corner of the area. Default to 0.
     *                                  $area['w']: The width of the area. Default to image width minus x-coordinate.
     *                                  $area['h']: The height of the area. Default to image height minus y-coordinate.
     *
     * @return array|bool
     */
    public static function getColor($sourceImage, $quality = 10, array $area = null)
    {
        $palette = static::getPalette($sourceImage, 5, $quality, $area);

        return $palette ? $palette[0] : false;
    }

    /**
     * Use the median cut algorithm to cluster similar colors.
     *
     * @bug Function does not always return the requested amount of colors. It can be +/- 2.
     *
     * @param mixed      $sourceImage   Path/URL to the image, GD resource, Imagick instance, or image as binary string
     * @param int        $colorCount    It determines the size of the palette; the number of colors returned.
     * @param int        $quality       1 is the highest quality.
     * @param array|null $area[x,y,w,h]
     *
     * @return array
     */
    public static function getPalette($sourceImage, $colorCount = 10, $quality = 10, array $area = null)
    {
        if ($colorCount < 2 || $colorCount > 256) {
            throw new \InvalidArgumentException("The number of palette colors must be between 2 and 256 inclusive.");
        }

        if ($quality < 1) {
            throw new \InvalidArgumentException("The quality argument must be an integer greater than one.");
        }

        $pixelArray = static::loadImage($sourceImage, $quality, $area);
        if (!count($pixelArray)) {
            throw new \RuntimeException("Unable to compute the color palette of a blank or transparent image.", 1);
        }

        // Send array to quantize function which clusters values
        // using median cut algorithm
        $cmap = static::quantize($pixelArray, $colorCount);
        $palette = $cmap->palette();

        return $palette;
    }

    /**
     * Histo: 1-d array, giving the number of pixels in each quantized region of color space
     *
     * @param array $pixels
     * @return array
     */
    private static function getHisto($pixels)
    {
        $histo = array();

        foreach ($pixels as $rgb) {
            list($red, $green, $blue) = static::getColorsFromIndex($rgb);
            $index = static::getColorIndex($red, $green, $blue);
            $histo[$index] = (isset($histo[$index]) ? $histo[$index] : 0) + 1;
        }

        return $histo;
    }

    /**
     * @param mixed $sourceImage Path/URL to the image, GD resource, Imagick instance, or image as binary string
     * @param int $quality
     * @param array|null $area
     * @return SplFixedArray
     */
    private static function loadImage($sourceImage, $quality, array $area = null)
    {
        $loader = new ImageLoader();
        $image  = $loader->load($sourceImage);
        $startX = 0;
        $startY = 0;
        $width  = $image->getWidth();
        $height = $image->getHeight();

        if ($area) {
            $startX = isset($area['x']) ? $area['x'] : 0;
            $startY = isset($area['y']) ? $area['y'] : 0;
            $width  = isset($area['w']) ? $area['w'] : ($width  - $startX);
            $height = isset($area['h']) ? $area['h'] : ($height - $startY);

            if ((($startX + $width) > $image->getWidth()) || (($startY + $height) > $image->getHeight())) {
                throw new \InvalidArgumentException("Area is out of image bounds.");
            }
        }

        $pixelCount = $width * $height;

        // Store the RGB values in an array format suitable for quantize function
        // SplFixedArray is faster and more memory-efficient than normal PHP array.
        $pixelArray = new SplFixedArray(ceil($pixelCount / $quality));

        $size = 0;
        for ($i = 0; $i < $pixelCount; $i = $i + $quality) {
            $x = $startX + ($i % $width);
            $y = (int)($startY + $i / $width);
            $color = $image->getPixelColor($x, $y);

            if (static::isClearlyVisible($color) && static::isNonWhite($color)) {
                $pixelArray[$size++] = static::getColorIndex($color->red, $color->green, $color->blue, 8);
                // TODO : Compute directly the histogram here ? (save one iteration over all pixels)
            }
        }

        $pixelArray->setSize($size);

        // Don't destroy a resource passed by the user !
        // TODO Add a method in ImageLoader to know if the image should be destroy
        // (or to know the detected image source type)
        if (is_string($sourceImage)) {
            $image->destroy();
        }

        return $pixelArray;
    }

    /**
     * @param object $color
     * @return bool
     */
    protected static function isClearlyVisible($color)
    {
        return $color->alpha <= self::THRESHOLD_ALPHA;
    }

    /**
     * @param object $color
     * @return bool
     */
    protected static function isNonWhite($color)
    {
        return !(
            $color->red > self::THRESHOLD_WHITE &&
            $color->green > self::THRESHOLD_WHITE &&
            $color->blue > self::THRESHOLD_WHITE
        );
    }

    /**
     * @param array $histo
     * @return VBox
     */
    private static function vboxFromHistogram(array $histo)
    {
        $rgbMin = array(PHP_INT_MAX, PHP_INT_MAX, PHP_INT_MAX);
        $rgbMax = array(0, 0, 0);

        // find min/max
        foreach ($histo as $index => $count) {
            $rgb = static::getColorsFromIndex($index, 0, ColorThief::SIGBITS);

            // For each color components
            for ($i = 0; $i < 3; ++$i) {
                if ($rgb[$i] < $rgbMin[$i]) {
                    $rgbMin[$i] = $rgb[$i];
                } elseif ($rgb[$i] > $rgbMax[$i]) {
                    $rgbMax[$i] = $rgb[$i];
                }
            }
        }

        return new VBox($rgbMin[0], $rgbMax[0], $rgbMin[1], $rgbMax[1], $rgbMin[2], $rgbMax[2], $histo);
    }

    /**
     * @param string $color
     * @param VBox $vBox
     * @param array $partialSum
     * @param int $total
     *
     * @return array
     */
    private static function doCut($color, $vBox, $partialSum, $total)
    {
        $dim1 = $color . '1';
        $dim2 = $color . '2';

        for ($i = $vBox->$dim1; $i <= $vBox->$dim2; $i++) {
            if ($partialSum[$i] > $total / 2) {
                $vBox1 = $vBox->copy();
                $vBox2 = $vBox->copy();
                $left = $i - $vBox->$dim1;
                $right = $vBox->$dim2 - $i;

                // Choose the cut plane within the greater of the (left, right) sides
                // of the bin in which the median pixel resides
                if ($left <= $right) {
                    $d2 = min($vBox->$dim2 - 1, intval($i + $right / 2));
                } else { /* left > right */
                    $d2 = max($vBox->$dim1, intval($i - 1 - $left / 2));
                }

                while (empty($partialSum[$d2])) {
                    $d2++;
                }
                // Avoid 0-count boxes
                while ($partialSum[$d2] >= $total && !empty($partialSum[$d2 - 1])) {
                    --$d2;
                }

                // set dimensions
                $vBox1->$dim2 = $d2;
                $vBox2->$dim1 = $d2 + 1;

                return array($vBox1, $vBox2);
            }
        }
    }

    /**
     * @param array $histo
     * @param VBox $vBox
     * @return array|void
     */
    private static function medianCutApply($histo, $vBox)
    {
        if (!$vBox->count()) {
            return;
        }

        // If the vbox occupies just one element in color space, it can't be split
        if ($vBox->count() == 1) {
            return array(
                $vBox->copy()
            );
        }

        // Select the longest axis for splitting
        $cutColor = $vBox->longestAxis();

        // Find the partial sum arrays along the selected axis.
        list($total, $partialSum) = static::sumColors($cutColor, $histo, $vBox);

        return static::doCut($cutColor, $vBox, $partialSum, $total);
    }

    /**
     * Find the partial sum arrays along the selected axis.
     *
     * @param string $axis r|g|b
     * @param array $histo
     * @param VBox $vBox
     * @return array [$total, $partialSum]
     */
    private static function sumColors($axis, $histo, $vBox)
    {
        $total = 0;
        $partialSum = array();

        // The selected axis should be the first range
        $colorIterateOrder = array_diff(array('r', 'g', 'b'), array($axis));
        array_unshift($colorIterateOrder, $axis);

        // Retrieves iteration ranges
        list($firstRange, $secondRange, $thirdRange) = static::getVBoxColorRanges($vBox, $colorIterateOrder);

        foreach ($firstRange as $firstColor) {
            $sum = 0;
            foreach ($secondRange as $secondColor) {
                foreach ($thirdRange as $thirdColor) {
                    list($red, $green, $blue) = static::rearrangeColors(
                        $colorIterateOrder,
                        $firstColor,
                        $secondColor,
                        $thirdColor
                    );
                    
                    $index = static::getColorIndex($red, $green, $blue);

                    if (isset($histo[$index])) {
                        $sum += $histo[$index];
                    }
                }
            }
            $total += $sum;
            $partialSum[$firstColor] = $total;
        }
        return array($total, $partialSum);
    }

    /**
     * @param array $order
     * @param int $color1
     * @param int $color2
     * @param int $color3
     * @return array
     */
    private static function rearrangeColors(array $order, $color1, $color2, $color3)
    {
        $data = array(
            $order[0] => $color1,
            $order[1] => $color2,
            $order[2] => $color3,
        );
        return array(
            $data['r'],
            $data['g'],
            $data['b']
        );
    }

    /**
     * @param VBox $vBox
     * @param array $order
     * @return array
     */
    private static function getVBoxColorRanges(VBox $vBox, array $order)
    {
        $ranges = array(
            'r' => range($vBox->r1, $vBox->r2),
            'g' => range($vBox->g1, $vBox->g2),
            'b' => range($vBox->b1, $vBox->b2)
        );

        return array(
            $ranges[$order[0]],
            $ranges[$order[1]],
            $ranges[$order[2]],
        );
    }

    /**
     * Inner function to do the iteration
     *
     * @param PQueue $priorityQueue
     * @param float $target
     * @param array $histo
     */
    private static function quantizeIter(&$priorityQueue, $target, $histo)
    {
        $nColors = 1;
        $nIterations = 0;

        while ($nIterations < static::MAX_ITERATIONS) {
            $vBox = $priorityQueue->pop();

            if (!$vBox->count()) { /* just put it back */
                $priorityQueue->push($vBox);
                $nIterations++;
                continue;
            }
            // do the cut
            $vBoxes = static::medianCutApply($histo, $vBox);

            if (!(is_array($vBoxes) && isset($vBoxes[0]))) {
                // echo "vbox1 not defined; shouldn't happen!"."\n";
                return;
            }

            $priorityQueue->push($vBoxes[0]);

            if (isset($vBoxes[1])) { /* vbox2 can be null */
                $priorityQueue->push($vBoxes[1]);
                $nColors++;
            }

            if ($nColors >= $target) {
                return;
            }

            if ($nIterations++ > static::MAX_ITERATIONS) {
                // echo "infinite loop; perhaps too few pixels!"."\n";
                return;
            }
        }
    }

    /**
     * @param SplFixedArray|array $pixels
     * @param $maxColors
     * @return bool|CMap
     */
    private static function quantize($pixels, $maxColors)
    {
        // short-circuit
        if (!count($pixels) || $maxColors < 2 || $maxColors > 256) {
            // echo 'wrong number of maxcolors'."\n";
            return false;
        }

        $histo = static::getHisto($pixels);

        // check that we aren't below maxcolors already
        //if (count($histo) <= $maxcolors) {
            // XXX: generate the new colors from the histo and return
        //}

        $vBox = static::vboxFromHistogram($histo);

        $priorityQueue = new PQueue(function ($a, $b) {
            return ColorThief::naturalOrder($a->count(), $b->count());
        });
        $priorityQueue->push($vBox);

        // first set of colors, sorted by population
        static::quantizeIter($priorityQueue, static::FRACT_BY_POPULATIONS * $maxColors, $histo);

        // Re-sort by the product of pixel occupancy times the size in color space.
        $priorityQueue->setComparator(function ($a, $b) {
            return ColorThief::naturalOrder($a->count() * $a->volume(), $b->count() * $b->volume());
        });

        // next set - generate the median cuts using the (npix * vol) sorting.
        static::quantizeIter($priorityQueue, $maxColors - $priorityQueue->size(), $histo);

        // calculate the actual colors
        $cmap = new CMap();

        for ($i = $priorityQueue->size(); $i > 0; $i--) {
            $cmap->push($priorityQueue->pop());
        }

        return $cmap;
    }
}
