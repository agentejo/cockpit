<?php

namespace Collections\Controller;

class Api extends \Cockpit\Controller {


    public function find(){

        $filter = $this->param("filter", null);
        $limit  = $this->param("limit", null);
        $sort   = $this->param("sort", null);
        $skip   = $this->param("skip", null);

        $docs = $this->app->data->common->collections->find($filter);

        if($limit) $docs->limit($limit);
        if($sort)  $docs->sort($sort);
        if($skip)  $docs->sort($skip);

        $docs = $docs->toArray();

        if(count($docs) && $this->param("extended", false)){
            foreach ($docs as &$doc) {
                $col = "collection".$doc["_id"];
                $doc["count"] = $this->app->data->collections->{$col}->count();
            }
        }

        return json_encode($docs);
    }

    public function findOne(){

        $filter = $this->param("filter", null);
        $doc    = $this->app->data->common->collections->findOne($filter);

        return $doc ? json_encode($doc) : '{}';
    }


    public function save(){

        $collection = $this->param("collection", null);

        if($collection) {

            $collection["modified"] = time();
            $collection["_uid"]     = @$this->user["_id"];

            if(!isset($collection["_id"])){
                $collection["created"] = $collection["modified"];
            }

            $this->app->data->common->collections->save($collection);
        }

        return $collection ? json_encode($collection) : '{}';
    }

    public function remove(){

        $collection = $this->param("collection", null);

        if($collection) {
            $col = "collection".$collection["_id"];

            $this->app->data->collections->dropCollection($col);
            $this->app->data->common->collections->remove(["_id" => $collection["_id"]]);
        }

        return $collection ? '{"success":true}' : '{"success":false}';
    }


    public function entries() {

        $collection = $this->param("collection", null);
        $entries    = [];

        if($collection) {

            $col = "collection".$collection["_id"];

            $filter = $this->param("filter", null);
            $limit  = $this->param("limit", null);
            $sort   = $this->param("sort", null);
            $skip   = $this->param("skip", null);

            $docs = $this->app->data->collections->{$col}->find($filter);

            if($limit) $docs->limit($limit);
            if($sort)  $docs->sort($sort);
            if($skip)  $docs->sort($skip);

            $entries = $docs->toArray();

        }

        return json_encode($entries);
    }

    public function removeentry(){

        $collection = $this->param("collection", null);
        $entryId    = $this->param("entryId", null);


        if($collection && $entryId) {

            $colid = $collection["_id"];

            $col = "collection".$collection["_id"];
            $this->app->data->collections->{$col}->remove(["_id" => $entryId]);
            $this->app->helper("versions")->remove("coentry:{$colid}-{$entryId}");
        }

        return ($collection && $entryId) ? '{"success":true}' : '{"success":false}';
    }

    public function saveentry(){

        $collection = $this->param("collection", null);
        $entry      = $this->param("entry", null);

        if($collection && $entry) {

            $col = "collection".$collection["_id"];

            $entry["modified"] = time();
            $entry["_uid"]     = @$this->user["_id"];

            if(!isset($entry["_id"])){
                $entry["created"] = $entry["modified"];
            } else {

                if($this->param("createversion", null)) {
                    $id    = $entry["_id"];
                    $colid = $collection["_id"];

                    $this->app->helper("versions")->add("coentry:{$colid}-{$id}", $entry);
                }
            }

            $this->app->data->collections->{$col}->save($entry);
        }

        return $entry ? json_encode($entry) : '{}';
    }

    // Versions

    public function getVersions() {

        $return = [];
        $id     = $this->param("id", false);
        $colid  = $this->param("colId", false);

        if($id && $colid) {

            $versions = $this->app->helper("versions")->get("coentry:{$colid}-{$id}");

            foreach ($versions as $uid => $data) {
                $return[] = ["time"=>$data["time"], "uid"=>$uid];
            }
        }

        return json_encode(array_reverse($return));

    }


    public function clearVersions() {

        $id     = $this->param("id", false);
        $colid  = $this->param("colId", false);

        if($id && $colid) {
            return '{"success":'.$this->app->helper("versions")->remove("coentry:{$colid}-{$id}").'}';
        }

        return '{"success":false}';
    }


    public function restoreVersion() {

        $versionId = $this->param("versionId", false);
        $docId     = $this->param("docId", false);
        $colId     = $this->param("colId", false);

        if($versionId && $docId && $colId) {

            if($versiondata = $this->app->helper("versions")->get("coentry:{$colId}-{$docId}", $versionId)) {
                $col = "collection".$colId;
                $this->app->data->collections->{$col}->save($versiondata["data"]);
                return '{"success":true}';
            }
        }

        return false;
    }


}