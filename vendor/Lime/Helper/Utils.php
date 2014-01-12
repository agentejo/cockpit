<?php

namespace Lime\Helper;

class Utils extends \Lime\Helper {

	public function gravatar($email, $size=40) {
		return "http://www.gravatar.com/avatar/".md5($email)."?d=mm&s=".$size;
	}

    public function formatSize($size) {
      $sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
      return ($size == 0) ? "n/a" : (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]);
    }

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
}