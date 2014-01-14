<?php

namespace Cockpit\Helper;

class Versions extends \Lime\Helper {

    protected $storage;
    public $maxVersions = 15;

    public function initialize(){

        $this->storage = new \RedisLite(sprintf("%s/cockpit.versions.sqlite", $this->app->path('data:')));
    }


    public function add($path, $data, $meta=[]) {

        $versions = $this->storage->hkeys($path);
        $count    = count($versions);

        if($count) {
          $prev = $this->storage->hget($path, $versions[$count-1]);

          if(json_encode($prev["data"]) === json_encode($data)) return;
        }

        if($count == $this->maxVersions) {
          $this->storage->hdel($path, $versions[0]);
        }

        return $this->storage->hset($path, uniqid(time()), ["data"=>$data, "meta"=>$meta, "time"=>time()]);
    }

    public function get($path, $uid = null) {

      if($uid) {
            return $this->storage->hget($path, $uid);
      } else {
            return $this->storage->hgetall($path);
      }
    }

    public function remove($path, $uid = null) {

      return $uid ? $this->storage->hdel($path, $uid) : $this->storage->del($path);

    }
}