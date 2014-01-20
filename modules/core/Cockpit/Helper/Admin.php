<?php

namespace Cockpit\Helper;

class Admin extends \Lime\Helper {

    public function initialize(){


    }

    public function menu($key, $def = null, $prio = 5){
        $lst = isset($this->app["admin.menu.{$key}"]) ? $this->app["admin.menu.{$key}"] : null;

        if($lst && $def) {
            $lst->insert(array_merge([
                "url"   => "",
                "label" => "",
                "title" => ""
            ], $def), (int)$prio);
        }

        return $lst;
    }
}