<?php

namespace Galleries\Controller;

class Api extends \Cockpit\Controller {

    public function find(){

        $options = [];

        if ($filter = $this->param("filter", null)) $options["filter"] = $filter;
        if ($limit  = $this->param("limit", null))  $options["limit"] = $limit;
        if ($sort   = $this->param("sort", null))   $options["sort"] = $sort;
        if ($skip   = $this->param("skip", null))   $options["skip"] = $skip;

        $docs = $this->app->db->find("common/galleries", $options);

        return json_encode($docs->toArray());
    }

    public function findOne(){

        $filter = $this->param("filter", []);
        $doc    = $this->app->db->findOne("common/galleries", $filter);

        return $doc ? json_encode($doc) : '{}';
    }

    public function save(){

        $gallery = $this->param("gallery", null);

        if ($gallery) {

            $gallery["modified"] = time();
            $gallery["_uid"]     = @$this->user["_id"];

            if (!isset($gallery["_id"])){
                $gallery["created"] = $gallery["modified"];
            }

            $this->app->db->save("common/galleries", $gallery);
        }

        return $gallery ? json_encode($gallery) : '{}';
    }

    public function update(){

        $criteria = $this->param("criteria", false);
        $data     = $this->param("data", false);

        if ($criteria && $data) {
            $this->app->db->update("common/galleries", $criteria, $data);
        }

        return '{"success":true}';
    }

    public function remove(){

        $gallery = $this->param("gallery", null);

        if ($gallery) {
            $this->app->db->remove("common/galleries", ["_id" => $gallery["_id"]]);
        }

        return $gallery ? '{"success":true}' : '{"success":false}';
    }

    public function updateGroups() {

        $groups = $this->param("groups", false);

        if ($groups !== false) {

            $this->app->memory->set("cockpit.galleries.groups", $groups);

            return '{"success":true}';
        }

        return false;
    }

    public function getGroups() {

        $groups = $this->app->memory->get("cockpit.galleries.groups", []);

        return json_encode($groups);
    }
}