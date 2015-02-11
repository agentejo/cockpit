<?php

namespace SimpleStorage;

class Client {

    protected $driver;

    public function __construct($server, $options=[]) {

        if (strpos($server, 'redis://')===0) {
            $this->driver = new Redis($server, $options);
        }

        if (strpos($server, 'redislite://')===0) {
            $this->driver = new RedisLite($server, $options);
        }

        if (strpos($server, 'file://')===0) {
            $this->driver = new File($server, $options);
        }
    }


    public function __call($method, $args) {

        return call_user_func_array([$this->driver, $method], $args);
    }
}