<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;

class FileStorage {

    protected $config  = [];
    protected $storages = [];
    protected $manager;

    public function __construct($config = []) {

        $this->manager = new MountManager();

        foreach ($config as $name => $_config) {
            $this->addStorage($name, $_config);
        }
    }

    public function addStorage($name, $config) {

        $this->config[$name] = $config;

        if (isset($config['mount']) && $config['mount']) {
            $this->initStorage($name);
        }

        return $this;
    }

    public function use($name) {

        if (!isset($this->storages[$name]) && isset($this->config[$name])) {
            $this->initStorage($name);
        }

        return $this->storages[$name] ?? null;
    }

    public function getURL($file) {
        $url = null;

        list($prefix, $path) = explode('://', $file, 2);

        if (isset($this->config[$prefix]['url'])) {

            if (!$path) {
                $url = $this->config[$prefix]['url'];
            } elseif ($this->has($file)) {
                $url = rtrim($this->config[$prefix]['url'], '/').'/'.ltrim($path, '/');
            }
        }

        return $url;
    }

    protected function initStorage($name) {

        $config = $this->config[$name];
        $adapter = new \ReflectionClass($config['adapter']);
        $this->storages[$name] = new Filesystem($adapter->newInstanceArgs($config['args'] ?: []));

        if (isset($config['mount']) && $config['mount']) {
            $this->manager->mountFilesystem($name, $this->storages[$name]);
        }

        return $this->storages[$name];
    }

    public function __call($name, $args) {

        return call_user_func_array([$this->manager, $name], $args);
    }
}
