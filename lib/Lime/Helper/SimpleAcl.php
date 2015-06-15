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

  public function hasaccess($groups, $resource, $actions){

    $groups  = (array) $groups;
    $actions = (array) $actions;

    if(!isset($this->resources[$resource])){
        return false;
    }

    foreach($groups as $g){

        if(!isset($this->groups[$g])) continue;
        if($this->groups[$g]==true) return true; // isSuperAdmin

        foreach($actions as $action){
          if(isset($this->rights[$g][$resource][$action])) {
              return true;
          }
        }
    }

    return false;
  }

}