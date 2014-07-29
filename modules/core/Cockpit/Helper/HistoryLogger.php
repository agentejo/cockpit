<?php

namespace Cockpit\Helper;

class HistoryLogger extends \Lime\Helper {

    public function load($options = []) {

        $options = array_merge([
            "from"  => false,
            "to"    => false,
            "limit" => 10,
            "sort"  => ["time" => -1]
        ], $options);

        $config = [];

        $config["limit"] = $options["limit"];
        $config["sort"]  = $options["sort"] ;

        if ($options["from"]) $config["filter"]["time"] = ['$gte' => $options["from"]];
        if ($options["to"])   $config["filter"]["time"] = ['$lte' => $options["to"]];

        return $this->app->db->find("cockpit/history", $config)->toArray();
    }

    public function clear($before = false) {

        return $this->app->db->remove("cockpit/history", $before ? ["time" => ['$lte'=>$before]] : []);
    }

    public function log($entry) {

        $entry = array_merge([
            "msg"    => "",
            "args"   => [],
            "url"    => false,
            "meta"   => [],
            "mod"    => "",
            "type"   => "info",
            "acl"    => "*",
            "uid"    => $this->app->module("auth")->getUser(),
            "time"   => time(),
        ], $entry);

        $this->app->db->insert("cockpit/history", $entry);
    }
}