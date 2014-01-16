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
            $region["_uid"]     = @$this->user["_id"];

            if(!isset($region["_id"])){
                $region["created"] = $region["modified"];
            } else {

                if($this->param("createversion", null) && isset($region["fields"], $region["tpl"])) {
                    $id = $region["_id"];
                    $this->app->helper("versions")->add("regions:{$id}", $region);
                }
            }

            $this->app->data->common->regions->save($region);
        }

        return $region ? json_encode($region) : '{}';
    }

    public function remove(){

        $region = $this->param("region", null);

        if($region) {
            $this->app->data->common->regions->remove(["_id" => $region["_id"]]);
            $this->app->helper("versions")->remove("regions:".$region["_id"]);
        }

        return $region ? '{"success":true}' : '{"success":false}';
    }

    public function getVersions() {

        $return = [];

        if($id = $this->param("id", false)) {

            $versions = $this->app->helper("versions")->get("regions:{$id}");

            foreach ($versions as $uid => $data) {
                $return[] = ["time"=>$data["time"], "uid"=>$uid];
            }
        }

        return json_encode(array_reverse($return));

    }

    public function clearVersions() {

        if($id = $this->param("id", false)) {
            return '{"success":'.$this->app->helper("versions")->remove("regions:{$id}").'}';
        }

        return '{"success":false}';
    }


    public function restoreVersion() {

        $versionId = $this->param("versionId", false);
        $docId     = $this->param("docId", false);

        if($versionId && $docId) {

            if($versiondata = $this->app->helper("versions")->get("regions:{$docId}", $versionId)) {
                $this->app->data->common->regions->save($versiondata["data"]);
                return '{"success":true}';
            }
        }

        return false;
    }
}