<?php

namespace Collections\Controller;

class RestApi extends \LimeExtra\Controller {

    public function get($collection=null) {

        if(!$collection) {
            return false;
        }

        $collection = $this->app->data->common->collections->findOne(["name"=>$collection]);

        if(!$collection) {
            return false;
        }

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

}