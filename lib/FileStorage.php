<?php

use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;

class FileStorage {

    protected $config  = [];
    protected $storages = [];
    protected $manager;

    public function __construct($config = []) {

        $this->config = $config;
        $this->manager = new MountManager();

        foreach ($config as $prefix => $c) {
            if (isset($c['mount']) && $c['mount']) {
                $this->use($prefix);
            }
        }
    }

    public function use($name) {

        if (!isset($this->storages[$name]) && isset($this->config[$name])) {

            $config = $this->config[$name];
            $adapter = new \ReflectionClass($config['adapter']);
            $this->storages[$name] = new Filesystem($adapter->newInstanceArgs($config['args'] ?: []));

            if (isset($config['mount']) && $config['mount']) {
                $this->manager->mountFilesystem($name, $this->storages[$name]);
            }
        }

        return $this->storages[$name] ?: null;
    }

    public function getURL($file) {
        $url = null;

        list($prefix, $path) = explode('://', $file, 2);

        if (isset($this->config[$prefix]['url'])) {

            if (!$path) {
                $url = $this->config[$prefix]['url'];
            } elseif ($this->has($file)) {
                $url = rtrim($this->config[$prefix]['url'], '/').'/'.$path;
            }
        }

        return $url;
    }

    public function __call($name, $args) {

        return call_user_func_array([$this->manager, $name], $args);
    }
}
