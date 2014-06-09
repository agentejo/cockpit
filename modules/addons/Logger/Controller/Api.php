<?php

namespace Logger\Controller;

class Api extends \Cockpit\Controller {

    public function find(){

        //$logentries = $this->app->db->find("addons/logger");
        $logentries = $this->app->db->find("addons/logger", ["limit"=> 3, "sort"=>["created"=>-1] ])->toArray();
        
        return json_encode($logentries);
    }
    
    public function save($logentry=null){
      
      error_log(__METHOD__.':'.$logentry);

        if (empty($logentry))
          $logentry = $this->param("logentry", null);

        if($logentry) {

            $logentry["modified"] = time();
            $logentry["_uid"]     = $this->user["_id"];

            if(!isset($logentry["_id"])){
                $logentry["created"] = $logentry["modified"];
            }

            $this->app->db->save("addons/logger", $logentry);
        }

        return $logentry ? json_encode($logentry) : '{}';
    } // save
    
    public function remove() {
        $id = $this->param("id", null);

        if($id) {
            $this->app->db->remove("addons/logger", ["_id" => $id]);
        }

        return $id ? '{"success":true}' : '{"success":false}';
    }
}