<?php

namespace Collections\Controller;

class Collections extends \Cockpit\Controller {


	public function index() {
		return $this->render("collections:views/index.php");
	}

    public function collection($id = null) {

        if (!$this->app->module("auth")->hasaccess("Collections", 'manage.collections')) {
            return false;
        }

        $locales = $this->app->db->getKey("cockpit/settings", "cockpit.locales", []);

        return $this->render("collections:views/collection.php", compact('id', 'locales'));
    }


    public function entries($id) {

        $collection = $this->app->db->findOne("common/collections", ["_id" => $id]);

        if (!$collection) {
            return false;
        }

        $count = $this->app->module("collections")->collectionById($collection["_id"])->count();

        $collection["count"] = $count;

        return $this->render("collections:views/entries.php", compact('id', 'collection', 'count'));
    }

    public function entry($collectionId, $entryId=null) {

        $collection = $this->app->db->findOne("common/collections", ["_id" => $collectionId]);
        $entry      = null;

        if (!$collection) {
            return false;
        }

        if ($entryId) {
            $col   = "collection".$collection["_id"];
            $entry = $this->app->db->findOne("collections/{$col}", ["_id" => $entryId]);

            if (!$entry) {
                return false;
            }
        }

		$locales = $this->app->db->getKey("cockpit/settings", "cockpit.locales", []);

        return $this->render("collections:views/entry.php", compact('collection', 'entry', 'locales'));

    }

}
