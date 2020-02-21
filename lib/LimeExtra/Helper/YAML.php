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

use Spyc;

/**
 * Class YAML
 * @package Lime\Helper
 */
class YAML extends \Lime\Helper {

    /** @var string|bool - Cache path */
    protected $cachePath = false;

    /**
     * Set cache path
     * @param string|bool $path
     */
    public function setCachePath($path) {
        $this->cachePath = \is_string($path) ? \rtrim($path, "/\\") : $path;
    }

    /**
     * Create YAML from string
     * @param  string $string
     * @return array
     */
    public static function fromString($string) {

        return Spyc::YAMLLoadString($string);
    }

    /**
     * Create YAML from file
     * @param  string $file
     * @return array|mixed
     */
    public function fromFile($file) {

        if (\strpos($file, ':') !== false) {
            $file = $this->app->path($file);
        }

        if ($this->cachePath) {

            $cachedfile = $this->get_cached_file($file);

            if ($cachedfile) {

                return include($cachedfile);
            }
        }

        return Spyc::YAMLLoad($file);
    }

    /**
     * Convert to YAML
     * @param  array $array
     * @return string
     */
    public function toYAML($array) {
        return Spyc::YAMLDump((array)$array, false, false, true);
    }

    /**
     * Write to file
     * @param  string $file
     * @param  array $array
     * @return int
     */
    public function toFile($file, $array) {
        return \file_put_contents($file, $this->toYAML($array));
    }

    /**
     * Get cached file
     * @param  string $file
     * @return bool|string
     */
    protected function get_cached_file($file) {

        $cachedfile = $this->cachePath.'/'.basename($file).'.'.md5($file).'.php';

        if (!\file_exists($cachedfile)) {
            $cachedfile = $this->cache_file($file, $cachedfile, null);
        }

        if ($cachedfile) {

            $mtime = \filemtime($file);

            if (\filemtime($cachedfile)!=$mtime) {
                $cachedfile = $this->cache_file($file, $cachedfile, $mtime);
            }

            return $cachedfile;
        }

        return false;
    }

    /**
     * @param string $file
     * @param string $cachedfile
     * @param null $filemtime
     * @return bool
     */
    protected function cache_file($file, $cachedfile, $filemtime = null) {

        if (!$filemtime) {
            $filemtime = \filemtime($file);
        }

        $data = \var_export(Spyc::YAMLLoad($file), true);

        if (\file_put_contents($cachedfile, "<?php return {$data};")) {
            \touch($cachedfile,  $filemtime);
            return $cachedfile;
        }

        return false;
    }
}
