<?php

namespace ColorThief;

class VBox
{
    public $r1;
    public $r2;
    public $g1;
    public $g2;
    public $b1;
    public $b2;
    public $histo;

    private $volume = false;
    private $count;
    private $count_set = false;
    private $avg = false;

    public function __construct($r1, $r2, $g1, $g2, $b1, $b2, $histo)
    {
        $this->r1 = $r1;
        $this->r2 = $r2;
        $this->g1 = $g1;
        $this->g2 = $g2;
        $this->b1 = $b1;
        $this->b2 = $b2;
        $this->histo = $histo;
    }

    public function volume($force = false)
    {
        if (!$this->volume || $force) {
            $this->volume = (($this->r2 - $this->r1 + 1) * ($this->g2 - $this->g1 + 1) * ($this->b2 - $this->b1 + 1));
        }

        return $this->volume;
    }

    public function count($force = false)
    {
        if (!$this->count_set || $force) {
            $npix = 0;

            // Select the fastest way (i.e. with the fewest iterations) to count
            // the number of pixels contained in this vbox.
            if ($this->volume() > count($this->histo)) {
                // Iterate over the histogram if the size of this histogram is lower than the vbox volume
                foreach ($this->histo as $bucketIndex => $count) {
                    $rgbBuckets = ColorThief::getColorsFromIndex($bucketIndex, ColorThief::SIGBITS);
                    if ($this->contains($rgbBuckets, 0)) {
                        $npix += $count;
                    }
                }
            } else {
                // Or iterate over points of the vbox if the size of the histogram is greater than the vbox volume
                for ($redBucket = $this->r1; $redBucket <= $this->r2; $redBucket++) {
                    for ($greenBucket = $this->g1; $greenBucket <= $this->g2; $greenBucket++) {
                        for ($blueBucket = $this->b1; $blueBucket <= $this->b2; $blueBucket++) {
                            // The getColorIndex function takes RGB values instead of buckets. The left shift converts our bucket into its RGB value.
                            $bucketIndex = ColorThief::getColorIndex(
                                $redBucket << ColorThief::RSHIFT,
                                $greenBucket << ColorThief::RSHIFT,
                                $blueBucket << ColorThief::RSHIFT,
                                ColorThief::SIGBITS
                            );
                            if (isset($this->histo[$bucketIndex])) {
                                $npix += $this->histo[$bucketIndex];
                            }
                        }
                    }
                }
            }
            $this->count = $npix;
            $this->count_set = true;
        }

        return $this->count;
    }

    public function copy()
    {
        return new self($this->r1, $this->r2, $this->g1, $this->g2, $this->b1, $this->b2, $this->histo);
    }

    /**
     * Calculates the average color represented by this VBox.
     *
     * @param bool $force
     *
     * @return array|bool
     */
    public function avg($force = false)
    {
        if (!$this->avg || $force) {
            $ntot = 0;
            $mult = 1 << ColorThief::RSHIFT;
            $rsum = 0;
            $gsum = 0;
            $bsum = 0;

            for ($redBucket = $this->r1; $redBucket <= $this->r2; $redBucket++) {
                for ($greenBucket = $this->g1; $greenBucket <= $this->g2; $greenBucket++) {
                    for ($blueBucket = $this->b1; $blueBucket <= $this->b2; $blueBucket++) {
                        // getColorIndex takes RGB values instead of buckets, so left shift so we get a bucketIndex
                        $bucketIndex = ColorThief::getColorIndex(
                            $redBucket << ColorThief::RSHIFT,
                            $greenBucket << ColorThief::RSHIFT,
                            $blueBucket << ColorThief::RSHIFT,
                            ColorThief::SIGBITS
                        );

                        // The bucket values need to be multiplied by $mult to get the RGB values.
                        // Can't use a left shift here, as we're working with a floating point number to put the value at the bucket's midpoint.
                        $hval = isset($this->histo[$bucketIndex]) ? $this->histo[$bucketIndex] : 0;
                        $ntot += $hval;
                        $rsum += ($hval * ($redBucket + 0.5) * $mult);
                        $gsum += ($hval * ($greenBucket + 0.5) * $mult);
                        $bsum += ($hval * ($blueBucket + 0.5) * $mult);
                    }
                }
            }

            if ($ntot) {
                $this->avg = [
                    (int) ($rsum / $ntot),
                    (int) ($gsum / $ntot),
                    (int) ($bsum / $ntot),
                ];
            } else {
                // echo 'empty box'."\n";
                $this->avg = [
                    (int) ($mult * ($this->r1 + $this->r2 + 1) / 2),
                    (int) ($mult * ($this->g1 + $this->g2 + 1) / 2),
                    (int) ($mult * ($this->b1 + $this->b2 + 1) / 2),
                ];

                // Ensure all channel values are leather or equal 255 (Issue #24)
                $this->avg = array_map(function ($val) {
                    return min($val, 255);
                }, $this->avg);
            }
        }

        return $this->avg;
    }

    public function contains(array $rgbValue, $rshift = ColorThief::RSHIFT)
    {
        // Get the buckets from the RGB values.
        $redBucket = $rgbValue[0] >> $rshift;
        $greenBucket = $rgbValue[1] >> $rshift;
        $blueBucket = $rgbValue[2] >> $rshift;

        return
            $redBucket >= $this->r1 &&
            $redBucket <= $this->r2 &&
            $greenBucket >= $this->g1 &&
            $greenBucket <= $this->g2 &&
            $blueBucket >= $this->b1 &&
            $blueBucket <= $this->b2;
    }

    /**
     * Determines the longest axis.
     *
     * @return string
     */
    public function longestAxis()
    {
        // Color-Width for RGB
        $red = $this->r2 - $this->r1;
        $green = $this->g2 - $this->g1;
        $blue = $this->b2 - $this->b1;

        return $red >= $green && $red >= $blue ? 'r' : ($green >= $red && $green >= $blue ? 'g' : 'b');
    }
}
