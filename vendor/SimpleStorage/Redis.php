<?php

namespace SimpleStorage;

class Redis {

    protected $client;

    public function __construct($server, $options=[]) {

        $server = explode(':', str_replace('redis://', '', $server));

        $this->client = new \Redis();

        $this->client->connect($server[0], @$server[1]);

        if (isset($options['auth']) && $options['auth']) {
            $this->client->auth($options['auth']);
        }

        // use custom prefix on all keys
        if (isset($options['prefix']) && $options['prefix']) {
            $this->client->setOption(\Redis::OPT_PREFIX, $options['prefix']);
        }

        $this->client->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
    }

    public function __call($method, $args) {

        return call_user_func_array([$this->client, $method], $args);
    }
}