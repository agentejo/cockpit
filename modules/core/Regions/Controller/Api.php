<?php

namespace Regions\Controller;

class Api extends \Cockpit\Controller {

    public function find(){

        $options = [];

        if ($filter = $this->param("filter", null)) $options["filter"] = $filter;
        if ($limit  = $this->param("limit", null))  $options["limit"] = $limit;
        if ($sort   = $this->param("sort", null))   $options["sort"] = $sort;
        if ($skip   = $this->param("skip", null))   $options["skip"] = $skip;

        $docs = $this->app->db->find("common/regions", $options);

        return json_encode($docs->toArray());
    }

    public function findOne(){

        $doc = $this->app->db->findOne("common/regions", $this->param("filter", []));

        return $doc ? json_encode($doc) : '{}';
    }

    public function save(){

        $region = $this->param("region", null);

        if ($region) {

            $region["modified"] = time();
            $region["_uid"]     = @$this->user["_id"];

            if (!isset($region["_id"])){
                $region["created"] = $region["modified"];
            } else {

                if ($this->param("createversion", null) && isset($region["fields"], $region["tpl"])) {
                    $id = $region["_id"];
                    $this->app->helper("versions")->add("regions:{$id}", $region);
                }
            }

            $this->app->db->save("common/regions", $region);
        }

        return $region ? json_encode($region) : '{}';
    }

    public function update(){

        $criteria = $this->param("criteria", false);
        $data     = $this->param("data", false);

        if ($criteria && $data) {
            $this->app->db->update("common/regions", $criteria, $data);
        }

        return '{"success":true}';
    }

    public function duplicate(){

        $regionId = $this->param("regionId", null);

        if ($regionId) {

            $region = $this->app->db->findOneById("common/regions", $regionId);

            if ($region) {

                unset($region['_id']);
                $region["modified"] = time();
                $region["_uid"]     = @$this->user["_id"];
                $region["created"] = $region["modified"];

                $region["name"] .= ' (copy)';

                $this->app->db->save("common/regions", $region);

                return json_encode($region);
            }
        }

        return false;
    }

    public function remove(){

        $region = $this->param("region", null);

        if ($region) {
            $this->app->db->remove("common/regions", ["_id" => $region["_id"]]);
            $this->app->helper("versions")->remove("regions:".$region["_id"]);
        }

        return $region ? '{"success":true}' : '{"success":false}';
    }

    public function getVersions() {

        $return = [];

        if ($id = $this->param("id", false)) {

            $versions = $this->app->helper("versions")->get("regions:{$id}");

            foreach ($versions as $uid => $data) {
                $return[] = ["time"=>$data["time"], "uid"=>$uid];
            }
        }

        return json_encode(array_reverse($return));

    }

    public function clearVersions() {

        if ($id = $this->param("id", false)) {
            return '{"success":'.$this->app->helper("versions")->remove("regions:{$id}").'}';
        }

        return '{"success":false}';
    }


    public function restoreVersion() {

        $versionId = $this->param("versionId", false);
        $docId     = $this->param("docId", false);

        if ($versionId && $docId) {

            if ($versiondata = $this->app->helper("versions")->get("regions:{$docId}", $versionId)) {
                $this->app->db->save("common/regions", $versiondata["data"]);
                return '{"success":true}';
            }
        }

        return false;
    }

    public function updateGroups() {

        $groups = $this->param("groups", false);

        if ($groups !== false) {

            $this->app->memory->set("cockpit.regions.groups", $groups);

            return '{"success":true}';
        }

        return false;
    }

    public function getGroups() {

        $groups = $this->app->memory->get("cockpit.regions.groups", []);

        return json_encode($groups);
    }
}