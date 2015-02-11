<?php

namespace SimpleStorage;

class RedisLite {

    protected $client;

    public function __construct($server, $options=[]) {

        $this->client = new \RedisLite(str_replace('redislite://', '', $server));
    }

    public function __call($method, $args) {

        return call_user_func_array([$this->client, $method], $args);
    }

}