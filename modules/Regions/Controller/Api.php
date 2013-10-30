<?php

namespace Regions\Controller;

class Api extends \Cockpit\Controller {

    public function find(){

        $filter = $this->param("filter", null);
        $limit  = $this->param("limit", null);
        $sort   = $this->param("sort", null);
        $skip   = $this->param("skip", null);

        $docs = $this->app->data->common->regions->find($filter);

        if($limit) $docs->limit($limit);
        if($sort)  $docs->sort($sort);
        if($skip)  $docs->sort($skip);

        $docs = $docs->toArray();

        return json_encode($docs);
    }

    public function findOne(){

        $filter = $this->param("filter", null);
        $doc    = $this->app->data->common->regions->findOne($filter);

        return $doc ? json_encode($doc) : '{}';
    }

    public function save(){

        $region = $this->param("region", null);

        if($region) {

            $region["modified"] = time();

            if(!isset($region["_id"])){
                $region["created"] = $region["modified"];
            }

            $this->app->data->common->regions->save($region);
        }

        return $region ? json_encode($region) : '{}';
    }

    public function remove(){

        $region = $this->param("region", null);

        if($region) {
            $this->app->data->common->regions->remove(["_id" => $region["_id"]]);
        }

        return $region ? '{"success":true}' : '{"success":false}';
    }
}