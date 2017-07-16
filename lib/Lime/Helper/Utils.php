<?php

namespace Lime\Helper;

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
		return "//www.gravatar.com/avatar/".md5($email)."?d=mm&s=".$size;
	}

    /**
     * @param $size
     * @return string
     */
    public function formatSize($size) {
      $sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
      return ($size == 0) ? "n/a" : (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]);
    }

    /**
     * @param $content
     * @param string $base
     * @return mixed
     */
    public function fixRelativeUrls($content, $base = '/'){

        $protocols = '[a-zA-Z0-9\-]+:';
        $regex     = '#\s+(src|href|poster)="(?!/|' . $protocols . '|\#|\')([^"]*)"#m';

        preg_match_all($regex, $content, $matches);
        
        if (isset($matches[0])) {

            foreach ($matches[0] as $i => $match) {
                
                if (trim($matches[2][$i])) {
                    $content = str_replace($match, " {$matches[1][$i]}=\"{$base}{$matches[2][$i]}\"", $content);
                }
            }
        }

        //$content = preg_replace($regex, " $1=\"$base\$2\"", $content);

        // Background image.
        $regex     = '#style\s*=\s*[\'\"](.*):\s*url\s*\([\'\"]?(?!/|' . $protocols . '|\#)([^\)\'\"]+)[\'\"]?\)#m';
        $content   = preg_replace($regex, 'style="$1: url(\'' . $base . '$2$3\')', $content);

        return $content;

    }

    /**
     * @param $string
     * @param string $replacement
     * @param bool|true $tolower
     * @return mixed|string
     */
    public function sluggify($string, $replacement = '-', $tolower = true) {
        $quotedReplacement = preg_quote($replacement, '/');

        $merge = array(
            '/[^\s\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]/mu' => ' ',
            '/\\s+/' => $replacement,
            sprintf('/^[%s]+|[%s]+$/', $quotedReplacement, $quotedReplacement) => '',
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

        $string = preg_replace(array_keys($map), array_values($map), $string);

        return $tolower ? strtolower($string):$string;
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
        $original_count = count($data);
        while (count($new_data) < $original_count) {
            foreach ($data as $name => $dependencies) {
                if (!count($dependencies)) {
                    $new_data[] = $name;
                    unset($data[$name]);
                    continue;
                }

                foreach ($dependencies as $key => $dependency) {
                    if (in_array($dependency, $new_data)) {
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

		if (preg_match('/^('.$yes_words.')$/i', $string)) {
			return true;
		} else if (preg_match('/^('.$no_words.')$/i', $string)) {
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

		$ret        = substr($string, 0, $length);
		$last_space = strrpos($ret, ' ');

		if ($last_space !== false && $string != $ret) {
			$ret = substr($ret, 0, $last_space);
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
	public function url_get_contents ($url) {
        $content = '';
        if (function_exists('curl_exec')){
            $conn = curl_init($url);
            curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($conn, CURLOPT_FRESH_CONNECT,  true);
            curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($conn,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');
            curl_setopt($conn, CURLOPT_AUTOREFERER, true);
            curl_setopt($conn, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($conn, CURLOPT_VERBOSE, 0);
            $content = (curl_exec($conn));
            curl_close($conn);
        }
        if (!$content && function_exists('file_get_contents')){
            $content = @file_get_contents($url);
        }
        if (!$content && function_exists('fopen') && function_exists('stream_get_contents')){
            $handle  = @fopen ($url, "r");
            $content = @stream_get_contents($handle);
        }
        return $content;
	}
}
