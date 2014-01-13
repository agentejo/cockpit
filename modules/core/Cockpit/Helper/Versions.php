<?php

namespace Cockpit\Helper;

class Versions extends \Lime\Helper {

    protected $storage;

    public function initialize(){

        $this->storage = \RedisLite(sprintf("%s/cockpit.versions.sqlite", $this->app->path('data:')));
    }


    public function store($path, $data, $meta=[]) {
        
       return $this->storage->hset($path, uniqid(time()), ["data"=>$data, "meta"=>$meta, "time"=>time()]);
    }

    public function get($path, $uid = null) {
       
       if($uid) {
            return $this->storage->hget($path, $uid);
       } else {
            return $this->storage->hgetall($path);
       }    

       return $this->storage->hset($path, uniqid(time()), ["data"=>$data, "meta"=>$meta, "time"=>time()]);
    }

    protected function remove($path, $uid) {
        
        return $this->storage->hdel($path, $uid);
    }
}