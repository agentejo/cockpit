<?php

namespace Lime\Helper;

/**
 * Class SimpleAcl
 * @package Lime\Helper
 */
class SimpleAcl
{


    protected $resources = array();
    protected $groups    = array();
    protected $rights    = array();
    protected $vars      = array();

    /**
     * @param $group
     * @param bool
     */
    public function isSuperAdmin($group)
    {
        return isset($this->groups[$group]) &&  $this->groups[$group];
    }

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
    public function addGroup($name, $isSuperAdmin = false, $vars = array())
    {
        $this->groups[$name] = $isSuperAdmin;
        $this->vars[$name]   = $vars;
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
     * @return array
     */
    public function getVars($group)
    {
        return isset($this->vars[$group]) ? $this->vars[$group] : array();
    }

    /**
     * @return mixed
     */
    public function getVar($group, $key, $default = null)
    {
        return isset($this->vars[$group][$key]) ? $this->vars[$group][$key] : $default;
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
     * @param $group
     * @param $resource
     * @return mixed
     */
    public function getGroupRights($group, $resource)
    {
        if (!isset($this->groups[$group])) {
            return false;
        }

        return isset($this->rights[$group][$resource]) ? isset($this->rights[$group][$resource]) : $this->groups[$group];
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
