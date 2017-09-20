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
                foreach ($this->histo as $rgb => $count) {
                    $rgb_array = ColorThief::getColorsFromIndex($rgb, 0, ColorThief::SIGBITS);
                    if ($this->contains($rgb_array, 0)) {
                        $npix += $count;
                    }
                }
            } else {
                // Or iterate over points of the vbox if the size of the histogram is greater than the vbox volume
                for ($i = $this->r1; $i <= $this->r2; $i++) {
                    for ($j = $this->g1; $j <= $this->g2; $j++) {
                        for ($k = $this->b1; $k <= $this->b2; $k++) {
                            $index = ColorThief::getColorIndex($i, $j, $k);
                            if (isset($this->histo[$index])) {
                                $npix += $this->histo[$index];
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
        return new VBox($this->r1, $this->r2, $this->g1, $this->g2, $this->b1, $this->b2, $this->histo);
    }

    public function avg($force = false)
    {
        if (!$this->avg || $force) {
            $ntot = 0;
            $mult = 1 << (8 - ColorThief::SIGBITS);
            $rsum = 0;
            $gsum = 0;
            $bsum = 0;

            for ($i = $this->r1; $i <= $this->r2; $i++) {
                for ($j = $this->g1; $j <= $this->g2; $j++) {
                    for ($k = $this->b1; $k <= $this->b2; $k++) {
                        $histoindex = ColorThief::getColorIndex($i, $j, $k);
                        $hval = isset($this->histo[$histoindex]) ? $this->histo[$histoindex] : 0;
                        $ntot += $hval;
                        $rsum += ($hval * ($i + 0.5) * $mult);
                        $gsum += ($hval * ($j + 0.5) * $mult);
                        $bsum += ($hval * ($k + 0.5) * $mult);
                    }
                }
            }

            if ($ntot) {
                $this->avg = array(
                    intval($rsum / $ntot),
                    intval($gsum / $ntot),
                    intval($bsum / $ntot)
                );
            } else {
                // echo 'empty box'."\n";
                $this->avg = array(
                    intval($mult * ($this->r1 + $this->r2 + 1) / 2),
                    intval($mult * ($this->g1 + $this->g2 + 1) / 2),
                    intval($mult * ($this->b1 + $this->b2 + 1) / 2)
                );

                // Ensure all channel values are leather or equal 255 (Issue #24)
                $this->avg = array_map(function ($val) {
                    return min($val, 255);
                }, $this->avg);
            }
        }

        return $this->avg;
    }

    public function contains(array $pixel, $rshift = ColorThief::RSHIFT)
    {
        $rval = $pixel[0] >> $rshift;
        $gval = $pixel[1] >> $rshift;
        $bval = $pixel[2] >> $rshift;

        return
            $rval >= $this->r1 &&
            $rval <= $this->r2 &&
            $gval >= $this->g1 &&
            $gval <= $this->g2 &&
            $bval >= $this->b1 &&
            $bval <= $this->b2;
    }

    /**
     * Determines the longest axis
     * @return string
     */
    public function longestAxis()
    {
        $colorWidth['r'] = $this->r2 - $this->r1;
        $colorWidth['g'] = $this->g2 - $this->g1;
        $colorWidth['b'] = $this->b2 - $this->b1;
        return array_search(max($colorWidth), $colorWidth);
    }
}
