<?php

namespace Galleries\Controller;

class Api extends \Cockpit\Controller {

    public function find(){

        $filter = $this->param("filter", null);
        $limit  = $this->param("limit", null);
        $sort   = $this->param("sort", null);
        $skip   = $this->param("skip", null);

        $docs = $this->getCollection("common/galleries")->find($filter);

        if($limit) $docs->limit($limit);
        if($sort)  $docs->sort($sort);
        if($skip)  $docs->sort($skip);

        $docs = $docs->toArray();

        return json_encode($docs);
    }

    public function findOne(){

        $filter = $this->param("filter", null);
        $doc    = $this->getCollection("common/galleries")->findOne($filter);

        return $doc ? json_encode($doc) : '{}';
    }

    public function save(){

        $gallery = $this->param("gallery", null);

        if($gallery) {

            $gallery["modified"] = time();
            $gallery["_uid"]     = @$this->user["_id"];

            if(!isset($gallery["_id"])){
                $gallery["created"] = $gallery["modified"];
            }

            $this->getCollection("common/galleries")->save($gallery);
        }

        return $gallery ? json_encode($gallery) : '{}';
    }

    public function remove(){

        $gallery = $this->param("gallery", null);

        if($gallery) {
            $this->getCollection("common/galleries")->remove(["_id" => $gallery["_id"]]);
        }

        return $gallery ? '{"success":true}' : '{"success":false}';
    }
}