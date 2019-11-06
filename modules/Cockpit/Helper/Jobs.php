<?php

/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cockpit\Helper;

class Jobs extends \Lime\Helper {

    public function initialize(){

    }

    public function getJob() {
        $job = $this->app->storage->findOne('cockpit/jobs_queue', ['time'=>['$lt' => time()]]);
        return $job;
    }

    public function add($handle, $payload = null, $time = 0) {
        
        $job = [
            'handle' => $handle,
            'payload' => $payload,
            'time' => $time,
            '_created' => time()
        ];

        $this->app->storage->save('cockpit/jobs_queue', $job);

        return $job['_id'];
    }

    public function work() {

        while ($job = $this->getJob()) {
            $this->execute($job);
        }
    }

    public function execute($job) {

        if (is_callable($job['handle'])) {
            $job['handle']($job['payload']);
        } elseif (function_exists($job['handle'])) {
            call_user_func($job['handle'], $job['payload']);
        } elseif (class_exists($job['handle'])) {
            $class = $job['handle'];
            $obj = new $class();

            if (method_exists($obj, 'handle')) {
                call_user_func_array([$obj, 'handle'], [$job['payload']]);
            }
        }

        $this->app->storage->remove('cockpit/jobs_queue', ['_id' => $job['_id']]);
    }

}