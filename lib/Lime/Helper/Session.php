<?php

namespace Lime\Helper;

use function \Lime\fetch_from_array;

class Session extends \Lime\Helper {

    protected $initialized = false;
    public $name;

    public function init($sessionname=null){

        if ($this->initialized) return;

        if (!\strlen(\session_id())) {
            $this->name = $sessionname ? $sessionname : $this->app["session.name"];

            \session_name($this->name);
            \session_start();
        } else {
            $this->name = \session_name();
        }

        $this->initialized = true;
    }

    public function write($key, $value){
        $_SESSION[$key] = $value;
    }

    public function read($key, $default=null){
        return fetch_from_array($_SESSION, $key, $default);
    }

    public function delete($key){
        unset($_SESSION[$key]);
    }

    public function destroy(){
        \session_destroy();
    }
}