<?php

namespace Datastore\Controller;


class Api extends \Cockpit\Controller {


    public function find(){

        $options = [];

        if ($filter = $this->param("filter", null)) $options["filter"] = $filter;
        if ($limit  = $this->param("limit", null))  $options["limit"] = $limit;
        if ($sort   = $this->param("sort", null))   $options["sort"] = $sort;
        if ($skip   = $this->param("skip", null))   $options["skip"] = $skip;

        $docs = $this->app->db->find("common/datastore", $options);

        if (count($docs) && $this->param("extended", false)){
            foreach ($docs as &$doc) {
                $doc["count"] = $this->app->module("datastore")->collectionById($doc["_id"])->count();
            }
        }

        return json_encode($docs->toArray());
    }

    public function findOne(){

        $doc = $this->app->db->findOne("common/datastore", $this->param("filter", []));

        return $doc ? json_encode($doc) : '{}';
    }


    public function save(){

        $datastore = $this->param("table", null);

        if ($datastore) {

            $datastore["modified"] = time();
            $datastore["_uid"]     = @$this->user["_id"];

            if (!isset($datastore["_id"])){
                $datastore["created"] = $datastore["modified"];
            }

            $this->app->db->save("common/datastore", $datastore);
        }

        return $datastore ? json_encode($datastore) : '{}';
    }

    public function remove(){

        $datastore = $this->param("table", null);

        if ($datastore) {
            $collection = "datastore".$datastore["_id"];

            $this->app->db->dropCollection("datastore/{$collection}");
            $this->app->db->remove("common/datastore", ["_id" => $datastore["_id"]]);
        }

        return $datastore ? '{"success":true}' : '{"success":false}';
    }


    public function entries() {

        $datastore = $this->param("table", null);
        $entries    = [];

        if ($datastore) {

            $collection = "datastore".$datastore["_id"];
            $options    = [];

            if ($filter = $this->param("filter", null)) $options["filter"] = $filter;
            if ($limit  = $this->param("limit", null))  $options["limit"] = $limit;
            if ($sort   = $this->param("sort", null))   $options["sort"] = $sort;
            if ($skip   = $this->param("skip", null))   $options["skip"] = $skip;

            $entries = $this->app->db->find("datastore/{$collection}", $options);
        }

        return json_encode($entries->toArray());
    }

    public function removeentry(){

        $datastore = $this->param("table", null);
        $entryId   = $this->param("entryId", null);

        if ($datastore && $entryId) {

            $collection = "datastore".$datastore["_id"];

            $this->app->db->remove("datastore/{$collection}", ["_id" => $entryId]);
        }

        return ($datastore && $entryId) ? '{"success":true}' : '{"success":false}';
    }

    public function emptytable(){

        $datastore = $this->param("table", null);

        if ($datastore) {

            $collection = "datastore".$datastore["_id"];

            $this->app->db->remove("datastore/{$collection}", []);
        }

        return $datastore ? '{"success":true}' : '{"success":false}';
    }

    public function saveentry(){

        $datastore = $this->param("table", null);
        $entry     = $this->param("entry", null);

        if ($datastore && $entry) {

            $collection = "datastore".$datastore["_id"];

            $entry["modified"] = time();

            if (!isset($entry["_id"])){
                $entry["created"] = $entry["modified"];
            }

            $this->app->db->save("datastore/{$collection}", $entry);
        }

        return $entry ? json_encode($entry) : '{}';
    }

    public function export($tableId) {

        if (!$this->app->module("auth")->hasaccess("Datastore", 'manage.datastore')) {
            return false;
        }

        $datastore = $this->app->db->findOneById("common/datastore", $tableId);

        if (!$datastore) return false;

        $collection = "datastore".$datastore["_id"];
        $entries    = $this->app->db->find("datastore/{$collection}");

        return json_encode($entries, JSON_PRETTY_PRINT);
    }

}