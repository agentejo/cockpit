<?php

namespace Cockpit\Helper;

class Revisions extends \Lime\Helper {

    protected $storage;

    public function initialize(){
        $this->storage = $this->app->storage;
    }


    public function count($id) {
        return $this->storage->count("cockpit/revisions", ["_oid" => $id]);
    }

    public function getList($id) {

        return $this->storage->find("cockpit/revisions", [
            "filter" => ["_oid" => $id],
            "sort"   => ["_created" => -1]
        ])->toArray();
    }

    public function add($id, $data, $meta = null, $creator = null) {

        if ($creator === true) {
            $creator = null;
            $user = $this->app->module('cockpit')->getUser();

            if ($user) {
                $creator = $user['_id'];
            }
        }

        $entry = [
            '_oid' => $id,
            'data' => $data,
            'meta' => $meta,
            '_creator' => $creator,
            '_created' => microtime(true)
        ];

        $this->storage->insert("cockpit/revisions", $entry);

        return $entry;
    }

    public function get($id) {
        return $this->storage->findOne("cockpit/revisions", ["_oid" => $id]);
    }

    public function remove($rid) {
        return $this->storage->remove("cockpit/revisions", ["_id" => $rid]);
    }

    public function removeAll($id) {
        return $this->storage->remove("cockpit/revisions", ["_oid" => $id]);
    }
}