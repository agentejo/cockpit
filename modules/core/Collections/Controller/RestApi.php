<?php

namespace Collections\Controller;

class RestApi extends \LimeExtra\Controller {

      /**
       * RCH 20140604
       * Check Collection definition for any 'approval' fields.
       * if any of those fields are not set to true then the entry will not be returned
       * Need ideally to iterate through versions of the entry to find the latest fully approved version
       */
    public function get($collection=null) {

        if(!$collection) {
            return false;
        }

        $collection = $this->app->db->findOne("common/collections", ["name"=>$collection]);

        if(!$collection) {
            return false;
        }

        $entries = [];

        if($collection) {

            $col     = "collection".$collection["_id"];
            $options = [];

            if($filter = $this->param("filter", null)) $options["filter"] = $filter;
            if($limit  = $this->param("limit", null))  $options["limit"] = $limit;
            if($sort   = $this->param("sort", null))   $options["sort"] = $sort;
            if($skip   = $this->param("skip", null))   $options["skip"] = $skip;

            // RCH 20140604
            if($approved = $this->param("approved", null)) {
              // Find all the approval fields for this collection
              //return('collection = '.print_r($collection,true));
              $approvedOnly = array();
              foreach($collection['fields'] as $field) {
                // is this field an approval field?
                if ($field['type']=='approval')
                  $approvedOnly[$field['name']]=1; // only include entries where this field is true
              }
              if (!empty($options["filter"]))
                $options["filter"] = array_merge($options["filter"], $approvedOnly);
              else 
                $options["filter"] = $approvedOnly;
            }
            //return('options = '.print_r($options,true));

            $entries = $this->app->db->find("collections/{$col}", $options);
        }

        return json_encode($entries->toArray());
    }

}