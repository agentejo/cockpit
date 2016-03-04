<?php

namespace Collections\Controller;

class RestApi extends \LimeExtra\Controller {
    private function get_item_from_hash($propName, $val) {
        static $cache = [];
        if (substr($propName, 0, 1) != "_" && preg_match('/^[0-9a-fA-F]{24}$/', $val)) {
            if (!array_key_exists($val, $cache)) {
                if (!array_key_exists($propName, $cache)) {
                    $cache[$propName] = $this->app->db->findOne("common/collections",["name"=>$propName]);
                    $cache[$cache[$propName]["_id"]] = $cache[$propName];
                }
                $cache[$val] = $this->denormalize_entry($this->app->db->findOneById("collections/collection".$cache[$propName]["_id"], $val));
            }
            if ($cache[$val]) {
                return $cache[$val];
            }
        }
        return $val;
    }

    private function denormalize_entry($entry) {
        if ($entry) {
            foreach($entry as $propName => $val) {
                // One gotcha of this function - $propName (and thus field name of collection link) must match the name of the collection a given entry belongs to. Otherwise denormalization will not know where to look for item hash and fail.
                if(is_array($val)) {
                    $result = $val;
                    foreach($val as $key=>$obj_id) {
                        $result[$key] = $this->get_item_from_hash($propName, $obj_id);
                    }
                    $entry[$propName] = $result;
                } else {
                    $entry[$propName] = $this->get_item_from_hash($propName, $val);
                }
            }
        }
        return $entry;
    }

    public function get($collection=null) {

        if (!$collection) {
            return false;
        }

        $collection = $this->app->db->findOne("common/collections", ["name"=>$collection]);

        if (!$collection) {
            return false;
        }

        $entries = [];

        if ($collection) {

            $col     = "collection".$collection["_id"];
            $options = [];

            if ($filter = $this->param("filter", null)) $options["filter"] = $filter;
            if ($limit  = $this->param("limit", null))  $options["limit"] = $limit;
            if ($sort   = $this->param("sort", null))   $options["sort"] = $sort;
            if ($skip   = $this->param("skip", null))   $options["skip"] = $skip;

            $entries = $this->app->db->find("collections/{$col}", $options);
        }


        if ($this->param("denormalize")) {
            foreach($entries as &$entry) {
                $entry = $this->denormalize_entry($entry);
            }
        }

        return json_encode($entries->toArray());
    }

}
