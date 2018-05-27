<?php

namespace League\ColorExtractor;

class Color
{
    /**
     * @param int  $color
     * @param bool $prependHash = true
     *
     * @return string
     */
    public static function fromIntToHex($color, $prependHash = true)
    {
        return ($prependHash ? '#' : '').sprintf('%06X', $color);
    }

    /**
     * @param string $color
     *
     * @return int
     */
    public static function fromHexToInt($color)
    {
        return hexdec(ltrim($color, '#'));
    }

    /**
     * @param int $color
     *
     * @return array
     */
    public static function fromIntToRgb($color)
    {
        return [
            'r' => $color >> 16 & 0xFF,
            'g' => $color >> 8 & 0xFF,
            'b' => $color & 0xFF,
        ];
    }

    /**
     * @param array $components
     *
     * @return int
     */
    public static function fromRgbToInt(array $components)
    {
        return ($components['r'] * 65536) + ($components['g'] * 256) + ($components['b']);
    }
}
