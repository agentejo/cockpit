<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lime\Helper;

/**
 * Class SimpleAcl
 * @package Lime\Helper
 */
class SimpleAcl
{
    protected $resources = [];
    protected $groups    = [];
    protected $rights    = [];
    protected $vars      = [];

    /**
     * Check if group is super admin
     * @param string $group
     * @param bool
     */
    public function isSuperAdmin($group)
    {
        return isset($this->groups[$group]) &&  $this->groups[$group];
    }

    /**
     * Add resource
     * @param string $resource
     * @param array $actions
     */
    public function addResource($resource, $actions = [])
    {
        $this->resources[$resource] = $actions;
    }

    /**
     * Add group
     * @param string $name
     * @param bool|false $isSuperAdmin
     */
    public function addGroup($name, $isSuperAdmin = false, $vars = [])
    {
        $this->groups[$name] = $isSuperAdmin;
        $this->vars[$name]   = $vars;
    }

    /**
     * Has group
     * @param string $group
     * @return bool
     */
    public function hasGroup($group)
    {
        return isset($this->groups[$group]);
    }

    /**
     * Get groups
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Get resources
     * @return array
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Get variables
     * @param string $group
     * @return array
     */
    public function getVars($group)
    {
        return isset($this->vars[$group]) ? $this->vars[$group] : [];
    }

    /**
     * Get variable
     * @param string $group
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getVar($group, $key, $default = null)
    {
        return isset($this->vars[$group][$key]) ? $this->vars[$group][$key] : $default;
    }

    /**
     * Allow group to access resource actions
     * @param string $group
     * @param string $resource
     * @param array $actions
     */
    public function allow($group, $resource, $actions = [])
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
     * Deny group to access resource actions
     * @param string $group
     * @param string $resource
     * @param array $actions
     */
    public function deny($group, $resource, $actions = [])
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
     * Get group resource rights
     * @param string $group
     * @param string $resource
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
     * Check if group has access to resource actions
     * @param string $groups
     * @param string $resource
     * @param array|string $actions
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
