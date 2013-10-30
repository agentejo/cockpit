<?php

namespace Collections\Controller;

class Collections extends \Cockpit\Controller {


	public function index() {
		return $this->render("collections:views/index.php");
	}

    public function collection($id = null) {

        return $this->render("collections:views/collection.php", compact('id'));
    }


    public function entries($id) {

        $collection = $this->app->data->common->collections->findOne(["_id" => $id]);

        if(!$collection) {
            return false;
        }

        $col   = "collection".$collection["_id"];
        $count = $this->app->data->collections->{$col}->count();

        $collection["count"] = $count;

        return $this->render("collections:views/entries.php", compact('id', 'collection', 'count'));
    }

    public function entry($collectionId, $entryId=null) {

        $collection = $this->app->data->common->collections->findOne(["_id" => $collectionId]);
        $entry      = null;

        if(!$collection) {
            return false;
        }

        if($entryId) {
            $col   = "collection".$collection["_id"];
            $entry = $this->app->data->collections->{$col}->findOne(["_id" => $entryId]);

            if(!$entry) {
                return false;
            }
        }

        return $this->render("collections:views/entry.php", compact('collection', 'entry'));

    }

}