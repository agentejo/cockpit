<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Handle php webserver (dev-only)
 *
 * usage: [ php -S localhost:8080 index.php ]
 *
 * @see http://php.net/manual/en/features.commandline.webserver.php
 */
if (PHP_SAPI == 'cli-server') {

    $path  = pathinfo($_SERVER["SCRIPT_FILENAME"]);
    $index = realpath($path['dirname'].'/index.php');
    $file  = $_SERVER["SCRIPT_FILENAME"];

    /* "dot" routes (see: https://bugs.php.net/bug.php?id=61286) */
    $_SERVER['PATH_INFO'] = $_SERVER['REQUEST_URI'];

    /* static files (eg. assetst/app/css/style.css) */
    if (is_file($file) && $path["extension"] != "php") {
        if ($path["extension"] == "tag") {
            header("Content-Type: application/javascript");
            readfile($file);
        }
        return false;
    }

    /* index files (eg. install/index.php) */
    if (is_file($index)) {
        include_once($index);
    }

}
