<?php

namespace Lime\Helper;


class SimpleAcl {


  protected $resources = array();
  protected $groups     = array();
  protected $rights    = array();


  public function addResource($resource, $actions = array()){
    $this->resources[$resource] = $actions;
  }


  public function addGroup($name, $isSuperAdmin = false){
    $this->groups[$name] = $isSuperAdmin;
  }

  public function hasGroup($group) {
    return isset($this->groups[$group]);
  }

  public function getGroups() {
    return $this->groups;
  }

  public function getResources() {
    return $this->resources;
  }

  public function allow($group, $resource, $actions = array()){

    $actions = (array)$actions;

    if(!count($actions)){
        $actions = $this->resources[$resource];
    }

    foreach($actions as &$action){
      $this->rights[$group][$resource][$action] = true;
    }

  }

  public function deny($group, $resource, $actions = array()){

      $actions = (array)$actions;

      if(!count($actions)){
          $actions = $this->resources[$resource];
      }

      foreach($actions as &$action){
          if(isset($this->rights[$group][$resource][$action])){
              unset($this->rights[$group][$resource][$action]);
          }
      }

  }


  public function hasaccess($group, $resource, $action){

    if(!isset($this->resources[$resource])){
        return false;
    }

    if(is_array($group)){
        foreach($group as $g){

            if(!isset($this->groups[$g])) continue;

            if($this->groups[$g]==true || isset($this->rights[$g][$resource][$action])) {
                return true;
            }
        }
    }else{

        if(!isset($this->groups[$group])) return false;

        if($this->groups[$group]==true || isset($this->rights[$group][$resource][$action])) {
            return true;
        }
    }

    return false;
  }

}