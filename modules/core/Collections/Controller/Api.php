<?php

namespace Collections\Controller;

class Api extends \Cockpit\Controller {


    public function find(){

        $options = [];

        if ($filter = $this->param("filter", null)) $options["filter"] = $filter;
        if ($limit  = $this->param("limit", null))  $options["limit"] = $limit;
        if ($sort   = $this->param("sort", null))   $options["sort"] = $sort;
        if ($skip   = $this->param("skip", null))   $options["skip"] = $skip;

        $docs = $this->app->db->find("common/collections", $options);

        if (count($docs) && $this->param("extended", false)){
            foreach ($docs as &$doc) {
                $doc["count"] = $this->app->module("collections")->collectionById($doc["_id"])->count();
            }
        }

        return json_encode($docs->toArray());
    }

    public function findOne(){

        $doc = $this->app->db->findOne("common/collections", $this->param("filter", []));

        return $doc ? json_encode($doc) : '{}';
    }


    public function save(){

        $collection = $this->param("collection", null);

        if ($collection) {

            $collection["modified"] = time();
            $collection["_uid"]     = @$this->user["_id"];

            if (!isset($collection["_id"])){
                $collection["created"] = $collection["modified"];
            }

            $this->app->db->save("common/collections", $collection);
        }

        return $collection ? json_encode($collection) : '{}';
    }

    public function update(){

        $criteria = $this->param("criteria", false);
        $data     = $this->param("data", false);

        if ($criteria && $data) {
            $this->app->db->update("common/collections", $criteria, $data);
        }

        return '{"success":true}';
    }

    public function remove(){

        $collection = $this->param("collection", null);

        if ($collection) {
            $col = "collection".$collection["_id"];

            $this->app->db->dropCollection("collections/{$col}");
            $this->app->db->remove("common/collections", ["_id" => $collection["_id"]]);
        }

        return $collection ? '{"success":true}' : '{"success":false}';
    }

    public function duplicate(){

        $collectionId = $this->param("collectionId", null);

        if ($collectionId) {

            $collection = $this->app->db->findOneById("common/collections", $collectionId);

            if ($collection) {

                unset($collection['_id']);
                $collection["modified"] = time();
                $collection["_uid"]     = @$this->user["_id"];
                $collection["created"] = $collection["modified"];

                $collection["name"] .= ' (copy)';

                $this->app->db->save("common/collections", $collection);

                return json_encode($collection);
            }
        }

        return false;
    }

    public function entries() {

        $collection = $this->param("collection", null);
        $entries    = [];

        if ($collection) {

            $col     = "collection".$collection["_id"];
            $options = [];

            if ($collection["sortfield"] && $collection["sortorder"]) {
                $options["sort"] = [];
                $options["sort"][$collection["sortfield"]] = (int)$collection["sortorder"];
            }

            if ($filter = $this->param("filter", null)) $options["filter"] = is_string($filter) ? json_decode($filter, true) : $filter;
            if ($limit  = $this->param("limit", null))  $options["limit"]  = $limit;
            if ($sort   = $this->param("sort", null))   $options["sort"]   = $sort;
            if ($skip   = $this->param("skip", null))   $options["skip"]   = $skip;

            $entries = $this->app->db->find("collections/{$col}", $options);
        }

        return json_encode($entries->toArray());
    }

    public function removeentry(){

        $collection = $this->param("collection", null);
        $entryId    = $this->param("entryId", null);


        if ($collection && $entryId) {

            $colid = $collection["_id"];
            $col   = "collection".$collection["_id"];

            $this->app->db->remove("collections/{$col}", ["_id" => $entryId]);

            $this->app->helper("versions")->remove("coentry:{$colid}-{$entryId}");
        }

        return ($collection && $entryId) ? '{"success":true}' : '{"success":false}';
    }

    public function emptytable(){

        $collection = $this->param("collection", null);

        if ($collection) {

            $collection = "collection".$collection["_id"];

            $this->app->db->remove("collections/{$collection}", []);
        }

        return $collection ? '{"success":true}' : '{"success":false}';
    }

    public function saveentry(){

        $collection = $this->param("collection", null);
        $entry      = $this->param("entry", null);

        if ($collection && $entry) {

            $entry["modified"] = time();
            $entry["_uid"]     = @$this->user["_id"];

            $col = "collection".$collection["_id"];

            if (!isset($entry["_id"])){
                $entry["created"] = $entry["modified"];
            } else {

                if ($this->param("createversion", null)) {
                    $id    = $entry["_id"];
                    $colid = $collection["_id"];

                    $this->app->helper("versions")->add("coentry:{$colid}-{$id}", $entry);
                }
            }

            $this->app->db->save("collections/{$col}", $entry);
        }

        return $entry ? json_encode($entry) : '{}';
    }

    // Versions

    public function getVersions() {

        $return = [];
        $id     = $this->param("id", false);
        $colid  = $this->param("colId", false);

        if ($id && $colid) {

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

        if ($id && $colid) {
            return '{"success":'.$this->app->helper("versions")->remove("coentry:{$colid}-{$id}").'}';
        }

        return '{"success":false}';
    }


    public function restoreVersion() {

        $versionId = $this->param("versionId", false);
        $docId     = $this->param("docId", false);
        $colId     = $this->param("colId", false);

        if ($versionId && $docId && $colId) {

            if ($versiondata = $this->app->helper("versions")->get("coentry:{$colId}-{$docId}", $versionId)) {

                $col = "collection".$colId;

                if ($entry = $this->app->db->findOne("collections/{$col}", ["_id" => $docId])) {
                    $this->app->db->save("collections/{$col}", $versiondata["data"]);
                    return '{"success":true}';
                }
            }
        }

        return false;
    }

    public function export($collectionId) {

        if (!$this->app->module("auth")->hasaccess("Collections", 'manage.collections')) {
            return false;
        }

        $collection = $this->app->db->findOneById("common/collections", $collectionId);

        if (!$collection) return false;

        $col     = "collection".$collection["_id"];
        $entries = $this->app->db->find("collections/{$col}");

        return json_encode($entries, JSON_PRETTY_PRINT);
    }

    public function updateGroups() {

        $groups = $this->param("groups", false);

        if ($groups !== false) {

            $this->app->memory->set("cockpit.collections.groups", $groups);

            return '{"success":true}';
        }

        return false;
    }

    public function getGroups() {

        $groups = $this->app->memory->get("cockpit.collections.groups", []);

        return json_encode($groups);
    }
}