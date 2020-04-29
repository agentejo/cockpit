<?php
// @codingStandardsIgnoreFile

if (!function_exists('json5_decode')) {
    /**
     * Takes a JSON encoded string and converts it into a PHP variable.
     *
     * The parameters exactly match PHP's json_decode() function - see
     * http://php.net/manual/en/function.json-decode.php for more information.
     *
     * @param string $source      The JSON string being decoded.
     * @param bool   $associative When TRUE, returned objects will be converted into associative arrays.
     * @param int    $depth       User specified recursion depth.
     * @param int    $options     Bitmask of JSON decode options.
     *
     * @return mixed
     */
    function json5_decode($source, $associative = false, $depth = 512, $options = 0)
    {
        return \ColinODell\Json5\Json5Decoder::decode($source, $associative, $depth, $options);
    }
}

// PHP 7.3 polyfills
if (!\defined('JSON_THROW_ON_ERROR')) {
    \define('JSON_THROW_ON_ERROR', 1 << 22);
}

if (!\class_exists('JsonException')) {
    class JsonException extends Exception
    {
    }
}
