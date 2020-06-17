<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LimeExtra\Helper;

/**
 * Class Utils
 * @package Lime\Helper
 */
class Utils extends \Lime\Helper {

    /**
     * @param $email
     * @param int $size
     * @return string
     */
    public function gravatar($email, $size=40) {
        return '//www.gravatar.com/avatar/'.\md5($email).'?d=mm&s='.$size;
    }

    /**
     * @param $size
     * @return string
     */
    public function formatSize($size) {
        $sizes = array(' Bytes', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB');
        return ($size == 0) ? 'n/a' : (\round($size/\pow(1024, ($i = \floor(\log($size, 1024)))), 2) . $sizes[$i]);
    }

    /**
     * Return max upload size
     *
     * @return int
     */
    public function getMaxUploadSize() {
        static $max_size = -1;

        if ($max_size < 0) {
            // Start with post_max_size.
            $post_max_size = $this->parseSize(ini_get('post_max_size'));
            if ($post_max_size > 0) {
                $max_size = $post_max_size;
            }

            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $upload_max = $this->parseSize(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }
        return $max_size;
    }

    /**
     * Parse size string
     *
     * @param string $size
     * @return void
     */
    public function parseSize($size) {

        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        else {
            return round($size);
        }
    }

    /**
     * @param $content
     * @param string $base
     * @return mixed
     */
    public function fixRelativeUrls($content, $base = '/') {

        $protocols = '[a-zA-Z0-9\-]+:';
        $regex     = '#\s+(src|href|poster)="(?!/|' . $protocols . '|\#|\')([^"]*)"#m';

        \preg_match_all($regex, $content, $matches);

        if (isset($matches[0])) {

            foreach ($matches[0] as $i => $match) {

                if (\trim($matches[2][$i])) {
                    $content = \str_replace($match, " {$matches[1][$i]}=\"{$base}{$matches[2][$i]}\"", $content);
                }
            }
        }

        //$content = preg_replace($regex, " $1=\"$base\$2\"", $content);

        // Background image.
        $regex     = '#style\s*=\s*[\'\"](.*):\s*url\s*\([\'\"]?(?!/|' . $protocols . '|\#)([^\)\'\"]+)[\'\"]?\)#m';
        $content   = \preg_replace($regex, 'style="$1: url(\'' . $base . '$2$3\')', $content);

        return $content;

    }

    /**
     * @param $string
     * @param string $replacement
     * @param bool|true $tolower
     * @return mixed|string
     */
    public function sluggify($string, $replacement = '-', $tolower = true) {
        $quotedReplacement = \preg_quote($replacement, '/');

        $merge = array(
            '/[^\s\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]/mu' => ' ',
            '/\\s+/' => $replacement,
            \sprintf('/^[%s]+|[%s]+$/', $quotedReplacement, $quotedReplacement) => '',
        );

        $map = array(
            '/ä|æ|ǽ/' => 'ae',
            '/ö|œ/' => 'oe',
            '/ü/' => 'ue',
            '/Ä/' => 'Ae',
            '/Ü/' => 'Ue',
            '/Ö/' => 'Oe',
            '/À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ/' => 'A',
            '/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª/' => 'a',
            '/Ç|Ć|Ĉ|Ċ|Č/' => 'C',
            '/ç|ć|ĉ|ċ|č/' => 'c',
            '/Ð|Ď|Đ/' => 'D',
            '/ð|ď|đ/' => 'd',
            '/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě/' => 'E',
            '/è|é|ê|ë|ē|ĕ|ė|ę|ě/' => 'e',
            '/Ĝ|Ğ|Ġ|Ģ/' => 'G',
            '/ĝ|ğ|ġ|ģ/' => 'g',
            '/Ĥ|Ħ/' => 'H',
            '/ĥ|ħ/' => 'h',
            '/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ/' => 'I',
            '/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı/' => 'i',
            '/Ĵ/' => 'J',
            '/ĵ/' => 'j',
            '/Ķ/' => 'K',
            '/ķ/' => 'k',
            '/Ĺ|Ļ|Ľ|Ŀ|Ł/' => 'L',
            '/ĺ|ļ|ľ|ŀ|ł/' => 'l',
            '/Ñ|Ń|Ņ|Ň/' => 'N',
            '/ñ|ń|ņ|ň|ŉ/' => 'n',
            '/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ/' => 'O',
            '/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º/' => 'o',
            '/Ŕ|Ŗ|Ř/' => 'R',
            '/ŕ|ŗ|ř/' => 'r',
            '/Ś|Ŝ|Ş|Š/' => 'S',
            '/ś|ŝ|ş|š|ſ/' => 's',
            '/Ţ|Ť|Ŧ/' => 'T',
            '/ţ|ť|ŧ/' => 't',
            '/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ/' => 'U',
            '/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ/' => 'u',
            '/Ý|Ÿ|Ŷ/' => 'Y',
            '/ý|ÿ|ŷ/' => 'y',
            '/Ŵ/' => 'W',
            '/ŵ/' => 'w',
            '/Ź|Ż|Ž/' => 'Z',
            '/ź|ż|ž/' => 'z',
            '/Æ|Ǽ/' => 'AE',
            '/ß/' => 'ss',
            '/Ĳ/' => 'IJ',
            '/ĳ/' => 'ij',
            '/Œ/' => 'OE',
            '/ƒ/' => 'f'
        ) + $merge;

        $string = \preg_replace(\array_keys($map), \array_values($map), $string);

        return $tolower ? \strtolower($string):$string;
    }

    /**
     * resolves complicated dependencies to determine what order something can run in
     *
     * start with an array like:
     * array(
     *     'a' => array('b', 'c'),
     *     'b' => array(),
     *     'c' => array('b')
     * )
     *
     * a depends on b and c, c depends on b, and b depends on nobody
     * in this case we would return array('b', 'c', 'a')
     *
     * @param array $data
     * @return array
     */
    public function resolveDependencies(array $data) {
        
        $new_data = array();
        $original_count = \count($data);
        while (\count($new_data) < $original_count) {
            foreach ($data as $name => $dependencies) {
                if (!\count($dependencies)) {
                    $new_data[] = $name;
                    unset($data[$name]);
                    continue;
                }

                foreach ($dependencies as $key => $dependency) {
                    if (\in_array($dependency, $new_data)) {
                        unset($data[$name][$key]);
                    }
                }
            }
        }
        return $new_data;
    }

    /**
    * Converts many english words that equate to true or false to boolean.
    *
    * Supports 'y', 'n', 'yes', 'no' and a few other variations.
    *
    * @param  string $string  The string to convert to boolean
    * @param  bool   $default The value to return if we can't match any
    *                          yes/no words
    * @return boolean
    */
    public function str_to_bool($string, $default = false) {

        $yes_words = 'affirmative|all right|aye|indubitably|most assuredly|ok|of course|okay|sure thing|y|yes+|yea|yep|sure|yeah|true|t|on|1|oui|vrai';
        $no_words  = 'no*|no way|nope|nah|na|never|absolutely not|by no means|negative|never ever|false|f|off|0|non|faux';

        if (\preg_match('/^('.$yes_words.')$/i', $string)) {
            return true;
        } else if (\preg_match('/^('.$no_words.')$/i', $string)) {
            return false;
        }

        return $default;
    }

    /**
    * Truncate a string to a specified length without cutting a word off.
    *
    * @param   string  $string  The string to truncate
    * @param   integer $length  The length to truncate the string to
    * @param   string  $append  Text to append to the string IF it gets
    *                           truncated, defaults to '...'
    * @return  string
    */
    public function safe_truncate($string, $length, $append = '...') {

        $ret        = \substr($string, 0, $length);
        $last_space = \strrpos($ret, ' ');

        if ($last_space !== false && $string != $ret) {
            $ret = \substr($ret, 0, $last_space);
        }

        if ($ret != $string ) {
            $ret .= $append;
        }

        return $ret;
    }

    /**
    * Get content from url source.
    *
    * @param   string  $url
    * @return  string
    */
    public function url_get_contents($url) {

        $content = '';

        if (\function_exists('curl_exec')){
            $conn = \curl_init($url);
            \curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, true);
            \curl_setopt($conn, CURLOPT_FRESH_CONNECT,  true);
            \curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
            \curl_setopt($conn,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');
            \curl_setopt($conn, CURLOPT_AUTOREFERER, true);
            \curl_setopt($conn, CURLOPT_FOLLOWLOCATION, 1);
            \curl_setopt($conn, CURLOPT_VERBOSE, 0);
            $content = (\curl_exec($conn));
            \curl_close($conn);
        }
        if (!$content && \function_exists('file_get_contents')){
            $content = @\file_get_contents($url);
        }
        if (!$content && \function_exists('fopen') && function_exists('stream_get_contents')){
            $handle  = @\fopen ($url, "r");
            $content = @\stream_get_contents($handle);
        }
        return $content;
    }

    public function buildTree(array $elements, $options = [], $parentId = null) {

        $options = \array_merge([
            'parent_id_column_name' => '_pid',
            'children_key_name' => 'children',
            'id_column_name' => '_id',
            'sort_column_name' => null
        ], $options);

        $branch = [];

        foreach ($elements as $element) {

            $pid = isset($element[$options['parent_id_column_name']]) ? $element[$options['parent_id_column_name']] : null;

            if ($pid == $parentId) {

                $element[$options['children_key_name']] = [];
                $children = $this->buildTree($elements, $options, $element[$options['id_column_name']]);

                if ($children) {
                    $element[$options['children_key_name']] = $children;
                }

                $branch[] = $element;
            }
        }

        if ($options['sort_column_name']) {

            \usort($branch, function ($a, $b) use($options) {

                $_a = isset($a[$options['sort_column_name']]) ? $a[$options['sort_column_name']] : null;
                $_b = isset($b[$options['sort_column_name']]) ? $b[$options['sort_column_name']] : null;

                if ($_a == $_b) {
                    return 0;
                }

                return ($_a < $_b) ? -1 : 1;
            });
        }

        return $branch;
    }

    public function buildTreeList($items, $options = [], $parent = null, $result = null, $depth = 0, $path = '-') {

        $options = \array_merge([
              'parent_id_column_name' => '_pid',
              'id_column_name' => '_id'
        ], $options);

        if (!$result) {
            $result = new \ArrayObject([]);
        }

        foreach ($items as $key => &$item) {

            if ($item[$options['parent_id_column_name']] == $parent) {
                $item['_depth'] = $depth;
                $item['_path'] = $path.$item[$options['id_column_name']];
                $result[] = $item;
                $idx = \count($result) - 1;
                unset($items[$key]);
                $this->buildTreeList($items, $options, $item[$options['id_column_name']], $result, $depth + 1, "{$path}{$item[$options['id_column_name']]}-");
            }
        }

        if ($depth == 0) {

            foreach ($result as $i => $item) {
                $result[$i]['_isParent'] = isset($result[$i+1]) && $result[($i+1)][$options['parent_id_column_name']]===$item[$options['id_column_name']];
            }
        }

        return $depth == 0 ? $result->getArrayCopy() : $result;
    }

    /**
     * get access token from header
     * */
    public function getBearerToken() {

        $headers = null;
        $token   = null;
        $server  = $this->app->request->server;

        if (isset($server['Authorization'])) {
            $headers = \trim($server['Authorization']);
        } elseif (isset($server['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = \trim($server['HTTP_AUTHORIZATION']);
        } else {
            $requestHeaders = $this->app->request->headers;
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = \array_combine(\array_map('ucwords', \array_keys($requestHeaders)), \array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = \trim($requestHeaders['Authorization']);
            }
        }

        // HEADER: Get the access token from the header
        if ($headers) {
            if (\preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                $token = $matches[1];
            }
        }

        return $token;
    }

    /**
     * Check if string is valid email
     * @param  string  $email
     * @return boolean
     */
    public function isEmail($email) {

        if (\function_exists('idn_to_ascii')) {
            $email = @\idn_to_ascii($email);
        }

        return (bool) \filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Cast boolean string values to boolean
     * @param  mixed $input
     * @return mixed
     */
    public function fixStringBooleanValues(&$input) {

        if (!\is_array($input)) {

            if (\is_string($input) && ($input === 'true' || $input === 'false')) {
                $input = filter_var($input, FILTER_VALIDATE_BOOLEAN);
            }
            return $input;
        }

        foreach ($input as $k => $v) {

            if (\is_array($input[$k])) {
                $input[$k] = $this->fixStringBooleanValues($input[$k]);
            }

            if (\is_string($v) && ($v === 'true' || $v === 'false')) {
                $v = \filter_var($v, FILTER_VALIDATE_BOOLEAN);
            }

            $input[$k] = $v;
        }

        return $input;
    }

    /**
     * Cast numeric string values to numbers
     * @param  mixed $input
     * @return mixed
     */
    public function fixStringNumericValues(&$input) {

        if (!\is_array($input)) {

            if (\is_string($input) && \is_numeric($input)) {
                $input += 0;
            }
            return $input;
        }

        foreach ($input as $k => $v) {

            if (\is_array($input[$k])) {
                $input[$k] = $this->fixStringNumericValues($input[$k]);
            }

            if (\is_string($v) && \is_numeric($v)) {
                $v += 0;
            }

            $input[$k] = $v;
        }

        return $input;
    }

    /**
     * Execute callable with retry if it fails
     * @param  int $times
     * @param  callable $fn
     * @return null
     */
    public function retry($times, callable $fn) {

        retrybeginning:
        try {
            return $fn();
        } catch (\Exception $e) {
            if (!$times) {
                throw new \Exception($e->getMessage(), 0, $e);
            }
            $times--;
            goto retrybeginning;
        }
    }

    /**
     * var_export with bracket array notation
     * source: https://www.php.net/manual/en/function.var-export.php#122853
     *
     * @param [type] $expr
     * @param boolean $return
     * @return void
     */
    function var_export($expr, $return=false) {
        
        $export = var_export($expr, true);
        $array  = preg_split("/\r\n|\n|\r/", $export);
        $array  = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
        $export = join(PHP_EOL, array_filter(["["] + $array));
        
        if ($return) {
            return $export;
        }
        
        echo $export;
    }
}
