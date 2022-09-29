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
 * Handle php command line webserver (dev-only)
 *
 * usage: [ php -S localhost:8080 index.php ]
 *
 * @see http://php.net/manual/en/features.commandline.webserver.php
 */
if (PHP_SAPI == 'cli-server') {

    // ----------------------------------------------------------------------
    // File access
    // ----------------------------------------------------------------------

    // Deny access to application and system files from being viewed
    if (preg_match('#(composer\.(json|lock)|package\.json|(README|CONTRIBUTING)\.md|cp|Dockerfile|LICENSE|\.(sqlite|sdb|s3db|db|yaml|yml))$#', $_SERVER['REQUEST_URI'])) {
        header('HTTP/1.0 403 Forbidden');
        exit('HTTP/1.0 403 Forbidden');
    }

    // ----------------------------------------------------------------------
    // MIME Types
    // ----------------------------------------------------------------------

    /* handle static files (eg. assets/app/css/style.css) */

    $file  = $_SERVER['SCRIPT_FILENAME'];
    $ext   = pathinfo($file, PATHINFO_EXTENSION);

    // Allow any files or directories that exist to be displayed directly
    if (is_file($file)) {

        // custom Mime Types
        if ($ext == 'tag') {
            header('Content-Type: application/javascript');
            readfile($file);
            exit;
        }

        // default Mime Types.
        if ($ext != 'php') {
            return false;
        }

    }

    // ----------------------------------------------------------------------
    // Rewrite Engine
    // ----------------------------------------------------------------------

    /* rewrite other URLs to index.php */

    $htdocs = $_SERVER['DOCUMENT_ROOT'];
    $path   = str_replace('/', DIRECTORY_SEPARATOR, parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $index  = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'index.php';
    $folder = dirname($index);

    if (is_file($htdocs . $path)) {

        // Requested URI matches an existing "php" file
        $index = $path;

    } else {

        // Trasverse directories and search for "index.php"
        do {

            $script = $folder . DIRECTORY_SEPARATOR . 'index.php';

            if (is_file($htdocs . $script)) {
                $index = $script;
                break;
            }

            $folder = dirname($folder);

        } while ($folder !== DIRECTORY_SEPARATOR && $folder !== '.');

    }

    /* handle php files (eg. install/index.php) */

     $index = DIRECTORY_SEPARATOR . ltrim($index, DIRECTORY_SEPARATOR);
     $file  = $htdocs . $index;

    // Update $_SERVER variables to point to the correct index-file.
     $_SERVER['SCRIPT_FILENAME'] = $file;
     $_SERVER['SCRIPT_NAME']     = $index;
     $_SERVER['PHP_SELF']        = $index;

    // Fix "dot" routes (see: https://bugs.php.net/bug.php?id=61286)
     $_SERVER['PATH_INFO']       = $path;

    // Deny access to files and directories whose names begin with a period
    if (preg_match('#/\.|^\.(?!well-known/)#', $_SERVER['REQUEST_URI'])) {
        header('HTTP/1.0 403 Forbidden');
        exit('HTTP/1.0 403 Forbidden');
    }

    // Allow any "php" files that exist to be displayed directly
    if (is_file($file) && dirname($index) != DIRECTORY_SEPARATOR) {
        include_once $file;
        exit;
    }

    /* Code execution returns to the main "index.php" file */

}
