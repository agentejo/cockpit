<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!COCKPIT_CLI) return;

$lang     = $app->param('lang', null);
$language = $app->param('language', $lang);
$author   = $app->param('author', 'Cockpit CLI');

if (!$lang) {
    return CLI::writeln("--lang parameter is missing", false);
}

// settings
$extensions = ['php', 'md', 'html', 'js', 'tag'];
$strings    = [];
$dirs       = [COCKPIT_DIR.'/modules'];

// var_export with bracket array notation
// source: https://www.php.net/manual/en/function.var-export.php#122853
function varexport($expression, $return=FALSE) {
    $export = var_export($expression, TRUE);
    $array  = preg_split("/\r\n|\n|\r/", $export);
    $array  = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
    $export = join(PHP_EOL, array_filter(["["] + $array));
    if ((bool)$return) return $export; else echo $export;
}


foreach ($dirs as $dir) {

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(COCKPIT_DIR.'/modules'), RecursiveIteratorIterator::SELF_FIRST);

    foreach ($iterator as $file) {

        if (in_array($file->getExtension(), $extensions)) {

            $contents = file_get_contents($file->getRealPath());

            preg_match_all('/(?:\@lang|App\.i18n\.get|App\.ui\.notify)\((["\'])((?:[^\1]|\\.)*?)\1(,\s*(["\'])((?:[^\4]|\\.)*?)\4)?\)/', $contents, $matches);

            if (!isset($matches[2])) continue;

            foreach ($matches[2] as &$string) {
                $strings[$string] = $string;
            }

        }
    }
}

if (count($strings)) {

    $strings['@meta'] = [
        'language' => $language,
        'author'   => $author,
        'date' => [
            'shortdays'   => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'longdays'    => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            'shortmonths' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'longmonths'  => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
        ]
    ];

    if ($app->path("#config:cockpit/i18n/{$lang}.php")) {
        $langfile = include($app->path("#config:cockpit/i18n/{$lang}.php"));
        $strings  = array_merge($strings, $langfile);
    }

    ksort($strings);

    $app->helper('fs')->write("#config:cockpit/i18n/{$lang}.php", '<?php return '.varexport($strings, true).';');
}

CLI::writeln("Done! Language file created: config/cockpit/i18n/{$lang}.php", true);
