<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleStorage;

class Client {

    protected $driver;

    public function __construct($server, $options=[]) {

        if (strpos($server, 'redis://')===0) {

            $server = explode(':', str_replace('redis://', '', $server));

            $this->driver = new \Redis();

            $this->driver->connect($server[0], @$server[1]);

            if (isset($options['auth']) && $options['auth']) {
                $this->driver->auth($options['auth']);
            }

            // use custom prefix on all keys
            if (isset($options['prefix']) && $options['prefix']) {
                $this->driver->setOption(\Redis::OPT_PREFIX, $options['prefix']);
            }

            $this->driver->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);

        } elseif (strpos($server, 'redislite://')===0) {
            $this->driver = new \RedisLite(str_replace('redislite://', '', $server), $options);
        }
    }

    public function get($key, $default = false) {
        
        $val = $this->driver->get($key);

        if ($val === false) {
            return $default;
        }

        return $val;
    }


    public function __call($method, $args) {

        return call_user_func_array([$this->driver, $method], $args);
    }
}
