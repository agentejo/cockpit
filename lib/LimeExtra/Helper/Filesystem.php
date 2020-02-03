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


class Filesystem extends \Lime\Helper {

    /**
     * @return mixed
     */
    public function path($path) {
        return $this->app->path($path);
    }

    /**
     * @return array
     */
    public function ls() {
        $pattern = null;
        $dir     = null;

        $args = \func_get_args();
        $lst  = [];

        switch(\count($args)){
            case 0:
                $dir = \getcwd();
            case 1:
                $dir = (\strpos($args[0], ':')) ? $this->app->path($args[0]) : $args[0];
                break;
            case 2:
                $pattern = $args[0];
                $dir = (\strpos($args[1], ':')) ? $this->app->path($args[1]) : $args[1];
                break;
            default:
                return $lst;
        }

        if (!$dir || !\file_exists($dir)) {
            return $lst;
        }

        $iter = new \DirectoryIterator($dir);

        foreach ($iter as $file) {

            if ($file->isDot()) continue;
            if ($pattern && !fnmatch($pattern, $file->getBasename())) continue;

            $lst[] = $file->isDir() ? clone $file : new FileObject($file->getRealPath());

        }

        return $lst;
    }

    /**
     * @return bool|mixed
     */
    public function read() {

        $args = \func_get_args();

        if (!\count($args)) {
            return false;
        }

        $args[0] = \strpos($args[0], ':') ? $this->app->path($args[0]) : $args[0];

        return $args[0] ? \call_user_func_array('file_get_contents', $args) : '';
    }

    /**
     * @return bool|mixed
     */
    public function write() {

        $args = \func_get_args();

        if (!count($args)) {
            return false;
        }

        if (\strpos($args[0], ':') !== false && !$this->app->isAbsolutePath($args[0])) {

            list($namespace, $additional) = \explode(":",$args[0], 2);

            if (!$this->app->path("{$namespace}:")) {
                return false;
            }

            $args[0] = $this->app->path("{$namespace}:").$additional;
        }

        // create file path
        if (!\file_exists($args[0])) {
            $this->mkdir(\dirname($args[0]));
        }

        return \call_user_func_array('file_put_contents', $args);
    }

    /**
     * @param $path
     * @param int $mode
     * @return bool
     */
    public function mkdir($path, $mode = 0755) {

        if (\strpos($path, ':') !== false && !$this->app->isAbsolutePath($path)) {
            list($namespace, $additional) = \explode(":", $path, 2);
            $dir = $this->app->path("{$namespace}:").$additional;
        } else {
            $dir = $path;
        }

        if (!\is_dir($dir) && !@mkdir($dir, $mode, true)) {
            return false;
        }

        return true;
    }

    /**
     * @param $path
     * @throws \Exception
     */
    public function delete($path) {

        $path = $this->app->path($path);

        if (\is_file($path) || \is_link($path)) {
            $func = DIRECTORY_SEPARATOR === '\\' && \is_dir($path) ? 'rmdir' : 'unlink';
            if (!@$func($path)) {
                throw new \Exception("Unable to delete: {$path}.");
            }
        } elseif (\is_dir($path)) {
            foreach (new \FilesystemIterator($path) as $item) {
                $this->delete($item->getRealPath());
            }
            if (!@rmdir($path)) {
                throw new \Exception("Unable to delete directory: {$path}.");
            }
        }
    }

    /**
     * @param $path
     * @param $dest
     * @param bool|true $_init
     * @return bool
     */
    public function copy($path, $dest, $_init = true) {

        if ($_init) {
            if (\strpos($path, ':')) $path = $this->app->path($path);
            if (\strpos($dest, ':')) $dest = $this->app->path($dest);
        }

        if (\is_dir($path)) {

            @\mkdir($dest);

            $items = \scandir($path);

            if (\sizeof($items) > 0) {
                foreach($items as $file) {

                    if ($file == "." || $file == "..") continue;

                    if (\is_dir("{$path}/{$file}")) {
                        $this->copy("{$path}/{$file}", "{$dest}/{$file}", false);
                    } else {
                        \copy("{$path}/{$file}", "{$dest}/{$file}");
                    }
                }
            }

            return true;

        } elseif (\is_file($path)) {
            return \copy($path, $dest);
        }

        return false;
    }

    /**
     * @param $path
     * @param $newpath
     * @param bool|true $overwrite
     * @return bool
     * @throws \Exception
     */
    public function rename($path, $newpath, $overwrite = true) {

        $path = $this->app->path($path);

        if (!$overwrite && \file_exists($newpath)) {
            return false;

        } elseif (!\file_exists($path)) {
            return false;

        } else {
            $this->mkdir(dirname($newpath));
            $this->delete($newpath);
            if (!@\rename($path, $newpath)) {
               return false;
            }
        }

        return true;
    }

    /**
     * @param $dir
     * @return int
     */
    public function getDirSize($dir) {

        $size = 0;

        if ($path = $this->app->path($dir)) {

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {

                if (!$file->isFile() || $file->isLink()) continue;

                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * @param $dir
     * @param bool|false $selfremove
     * @return bool
     */
    public function removeEmptySubFolders($dir, $selfremove = false) {

        if ($path = $this->app->path($dir)) {

            $empty = true;

            foreach (\glob($path.DIRECTORY_SEPARATOR."*") as $file) {
                $empty &= \is_dir($file) && $this->removeEmptySubFolders($file, true);
            }

            return $empty && ($selfremove ? @rmdir($path) : true);
        }

        return false;
    }
}

/**
 * Use custom FileObject to prevent "too many files open" error
 */

class FileObject {

    protected $path;
    protected $fileObject;

    public function __construct($path) {
        $this->path = $path;
    }

    public function getFilename() {
        return \basename($this->path);
    }

    public function getPathName() {
        return $this->path;
    }

    public function getRealPath() {
        return \realpath($this->path);
    }

    public function getBasename($suffix = null) {
        return \basename($this->path, $suffix);
    }

    public function getSize() {
        return \filesize($this->path);
    }

    public function __call($method, $args) {

        if (!isset($this->fileObject)) {
            $this->fileObject = new \SplFileObject($this->path);
        }

        return \call_user_func_array([$this->fileObject, $method], $args);
    }
}


if (!function_exists('fnmatch')) {
    function fnmatch($pattern, $string){
        return \preg_match("#^".\strtr(\preg_quote($pattern, '#'), ['\*' => '.*', '\?' => '.'])."$#i", $string);
    }
}
