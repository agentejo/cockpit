<?php

namespace Collections\Controller;

class Collections extends \Cockpit\Controller {


	public function index() {
		return $this->render("collections:views/index.php");
	}

    public function collection($id = null) {

        if(!$this->app->module("auth")->hasaccess("Collections", 'manage.collections')) {
            return false;
        }

        return $this->render("collections:views/collection.php", compact('id'));
    }


    public function entries($id) {

        $collection = $this->app->db->findOne("common/collections", ["_id" => $id]);

        if(!$collection) {
            return false;
        }
      
        // RCH 20140602
        if(!$this->app->module("auth")->hasaccess("Collections", 'manage.entries.list.'.$collection['name'])) {
            return false;
        }

        $count = $this->app->module("collections")->collectionById($collection["_id"])->count();

        $collection["count"] = $count;

        return $this->render("collections:views/entries.php", compact('id', 'collection', 'count'));
    }

    public function entry($collectionId, $entryId=null) {

        $collection = $this->app->db->findOne("common/collections", ["_id" => $collectionId]);
        $entry      = null;

        if(!$collection) {
            return false;
        }

       // RCH 20140602
       if(!$this->app->module("auth")->hasaccess("Collections", 'manage.entries.addedit.'.$collection['name'])) {
           return false;
       }

      
        if($entryId) {
            $col   = "collection".$collection["_id"];
            $entry = $this->app->db->findOne("collections/{$col}", ["_id" => $entryId]);

            if(!$entry) {
                return false;
            }
        }

        return $this->render("collections:views/entry.php", compact('collection', 'entry'));

    }

}