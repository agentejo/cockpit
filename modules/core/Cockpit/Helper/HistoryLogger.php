<?php

namespace Cockpit\Helper;

class HistoryLogger extends \Lime\Helper {

    public function load($options = []) {

        $options = array_merge([
            "from"  => false,
            "to"    => false,
            "limit" => 20,
            "order" => ["time" => -1]
        ], $options);

        $config[];

        $config["limit"]   = $options["limit"];
        $config["order"]   = $options["order"];

        if($options["from"]) $config["filter"]["time"] = ['$gte' => $options["from"]];
        if($options["to"])   $config["filter"]["time"] = ['$lte' => $options["to"]];

        return $this->app->db->find("cockpit/history", $config);
    }

    public function clear($before = false) {

        return $this->app->db->remove("cockpit/history", $before ? ["time" => ['$lte'=>$before]] : []);
    }

    public function log($opts) {

        $entry = array_merge([
            "message" => "",
            "params"  => [],
            "url"     => false,
            "meta"    => [],
            "time"    => time(),
            "uid"     => $this->app->module("auth")->getUser()
        ], $opts);

        $this->app->db->insert("cockpit/history", $entry);
    }
}