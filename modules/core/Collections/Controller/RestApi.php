<?php

namespace Collections\Controller;

class RestApi extends \LimeExtra\Controller {
  
      protected $storage;



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
              //error_log(__METHOD__.': collection = '.print_r($collection,true));
              $approvalFields = array();
              foreach($collection['fields'] as $field) {
                // is this field an approval field?
                if ($field['type']=='approval')
                  $approvalFields[]=$field['name']; // only include entries where this field is true
              } // foreach
              
              /*
              if (!empty($options["filter"]))
                $options["filter"] = array_merge($options["filter"], $approvalFields);
              else 
                $options["filter"] = $approvedOnly;
                */
            
              $allentries = $this->app->db->find("collections/{$col}", $options);
              $entries = array();
              
             // return('$allentries = '.print_r($allentries->toArray(),true));

              // Now check each entry for the the latest approved version
              foreach ($allentries->toArray() as $entry) {
                
                $approvals = 0;
                // check that each approval field has been approved in the current entry
                error_log(__METHOD__.': $entry = '.print_r($entry,true));
                foreach ($approvalFields as $approvalField) {
                  if (isset($entry[$approvalField]) && $entry[$approvalField]==true)
                    $approvals++;
                } // foreach

                if ($approvals==count($approvalFields)) {
                  error_log(__METHOD__.': The entry is APPROVED');
                  $entries[] = $entry;
                } else {
                  error_log(__METHOD__.': Search Versions for approved version');
                  // Current entry isn't approved, check versions
                  if ($latestApprovedVersion = $this->_getLatestApprovedVersion($entry["_id"], $collection["_id"]))                   {
                    // we've got an approved version
                    $entries[] = $latestApprovedVersion;
                  }
                }
              } // foreach
              
              return json_encode($entries);

            } else {
            //return('options = '.print_r($options,true));

              $entries = $this->app->db->find("collections/{$col}", $options);
            }
        }

        return json_encode($entries->toArray());
    }
  
  
  
      /**
       * Get the latest approved version of a particular entry
       */
      private function _getLatestApprovedVersion($entryid, $collectionid) {
        
        $this->_initializeVersionStorage();

        //$return = [];
        //$id     = $this->param("id", false);
        //$colid  = $this->param("colId", false);
        $id = $entryid;
        $colid = $collectionid;

        if($id && $colid) {

            $collection = $this->app->db->findOneById("common/collections", $colid);
                        
            $this->app->module("logger")->save(['message'=>'$collection = '.print_r($collection,true)]);
            //error_log('classname = '.get_class($this->app->module("logger")));
            //error_log('methods = '.print_r(get_class_methods(get_class($this->app->module("logger"))),true));

            //return('$collection = '.print_r($collection,true));
            $approvalFields = array();
            foreach($collection['fields'] as $field) {
              // is this field an approval field?
              if ($field['type']=='approval')
                $approvalFields[]=$field['name']; // only include entries where this field is true
            } // foreach

            //ersions = $this->app('versions')->get("coentry:{$colid}-{$id}");
            //$vs = $this->app('versions')->get(); // definitely does not work - no errors though
            $vs = $this->_getVersions("coentry:{$colid}-{$id}");

            error_log('$id='.$id.'  /  $versions = '.print_r($vs,true));
          
            if (!empty($vs)) {
              //error_log("coentry:{$colid}-{$id}");
              $vs = array_reverse($vs); // we need the latest version
              //$logger->save(['message'=>'versions test']);
              

              foreach ($vs as $uid => $thisversion) {
                error_log('$versions = '.print_r($thisversion,true));
                $approvals = 0;
                // check that each approval field has been approved in this version
                foreach ($approvalFields as $approvalField) {
                  if ($thisversion['data'][$approvalField])
                    $approvals++;
                } // foreach

                if ($approvals==count($approvalFields)) {
                  return $thisversion['data'];
                  // stop when we have the latest approved version of this entry
                }
              }
            }
        }

        // if we get this far there wasn't any approved versions
        return false;

    } // _getLatestApprovedVersion

  
    private function _initializeVersionStorage(){

        $this->storage = new \RedisLite(sprintf("%s/cockpit.versions.sqlite", $this->app->path('data:')));
    }

  
  private function _getVersions($path, $uid = null) {

      if($uid) {
            $stuff = $this->storage->hget($path, $uid);
      } else {
            $stuff = $this->storage->hgetall($path);         
      }
      //error_log(__METHOD__.': $stuff='.print_r($stuff,true));
           
      return $stuff;
    }

}