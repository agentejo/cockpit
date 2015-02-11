<?php

namespace Cockpit\Helper;

class Versions extends \Lime\Helper {

    protected $storage;
    public $maxVersions = 15;

    public function initialize(){

        $config = array_merge([
          'server'  => sprintf("redislite://%s/cockpit.versions.sqlite", $this->app->path('data:')),
          'options' => []
        ], $this->app->retrieve('app.config/cockpit.versions', []));

        $this->storage = new \SimpleStorage\Client($config['server'], $config['options']);
    }


    public function add($path, $data, $meta=[]) {

        $versions = $this->storage->hkeys($path);
        $count    = count($versions);

        if ($count) {
          $prev = $this->storage->hget($path, $versions[$count-1]);

          if (json_encode($prev["data"]) === json_encode($data)) return;
        }

        if ($count == $this->maxVersions) {
          $this->storage->hdel($path, $versions[0]);
        }

        return $this->storage->hset($path, uniqid(time()), ["data"=>$data, "meta"=>$meta, "time"=>time()]);
    }

    public function get($path, $uid = null) {

      if ($uid) {
            return $this->storage->hget($path, $uid);
      } else {
            return $this->storage->hgetall($path);
      }
    }

    public function remove($path, $uid = null) {

      return $uid ? $this->storage->hdel($path, $uid) : $this->storage->del($path);

    }
}