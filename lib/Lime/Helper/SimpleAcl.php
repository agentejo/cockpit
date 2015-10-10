<?php

namespace Lime\Helper;

/**
 * Class SimpleAcl
 * @package Lime\Helper
 */
class SimpleAcl
{


    protected $resources = array();
    protected $groups = array();
    protected $rights = array();


    /**
     * @param $resource
     * @param array $actions
     */
    public function addResource($resource, $actions = array())
    {
        $this->resources[$resource] = $actions;
    }

    /**
     * @param $name
     * @param bool|false $isSuperAdmin
     */
    public function addGroup($name, $isSuperAdmin = false)
    {
        $this->groups[$name] = $isSuperAdmin;
    }

    /**
     * @param $group
     * @return bool
     */
    public function hasGroup($group)
    {
        return isset($this->groups[$group]);
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return array
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * @param $group
     * @param $resource
     * @param array $actions
     */
    public function allow($group, $resource, $actions = array())
    {

        $actions = (array)$actions;

        if (!count($actions)) {
            $actions = $this->resources[$resource];
        }

        foreach ($actions as &$action) {
            $this->rights[$group][$resource][$action] = true;
        }

    }

    /**
     * @param $group
     * @param $resource
     * @param array $actions
     */
    public function deny($group, $resource, $actions = array())
    {

        $actions = (array)$actions;

        if (!count($actions)) {
            $actions = $this->resources[$resource];
        }

        foreach ($actions as &$action) {
            if (isset($this->rights[$group][$resource][$action])) {
                unset($this->rights[$group][$resource][$action]);
            }
        }

    }

    /**
     * @param $groups
     * @param $resource
     * @param $actions
     * @return bool
     */
    public function hasaccess($groups, $resource, $actions)
    {

        $groups = (array)$groups;
        $actions = (array)$actions;

        if (!isset($this->resources[$resource])) {
            return false;
        }

        foreach ($groups as $g) {

            if (!isset($this->groups[$g])) continue;
            if ($this->groups[$g] == true) return true; // isSuperAdmin

            foreach ($actions as $action) {
                if (isset($this->rights[$g][$resource][$action])) {
                    return true;
                }
            }
        }

        return false;
    }

}