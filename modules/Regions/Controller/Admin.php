<?php

namespace Regions\Controller;


class Admin extends \Cockpit\AuthController {


    public function index() {

        $regions = $this->module('regions')->getRegionsInGroup();

        foreach ($regions as $region => $meta) {
            $regions[$region]['allowed'] = [
                'delete' => $this->module('cockpit')->hasaccess('regions', 'delete'),
                'create' => $this->module('cockpit')->hasaccess('regions', 'create'),
                'region_edit' => $this->module('regions')->hasaccess($region, 'edit'),
                'region_form' => $this->module('regions')->hasaccess($region, 'form')
            ];
        }

        return $this->render('regions:views/index.php', compact('regions'));
    }

    public function region($name = null) {

        if ($name && !$this->module('regions')->hasaccess($name, 'edit')) {
            return $this->helper('admin')->denyRequest();
        }

        if (!$name && !$this->module('cockpit')->hasaccess('regions', 'create')) {
            return $this->helper('admin')->denyRequest();
        }

        $region = [ 'name'=>'', 'description' => '', 'fields'=>[], 'template' => '', 'data' => null];

        if ($name) {

            $region = $this->module('regions')->region($name);

            if (!$region) {
                return false;
            }
        }

        // acl groups
        $aclgroups = [];

        foreach ($this->app->helper("acl")->getGroups() as $group => $superAdmin) {

            if (!$superAdmin) $aclgroups[] = $group;
        }

        return $this->render('regions:views/region.php', compact('region', 'aclgroups'));
    }

    public function form($name = null) {

        if ($name) {

            $region = $this->module('regions')->region($name);

            if (!$region) {
                return false;
            }

            if (!$this->module('regions')->hasaccess($region['name'], 'form')) {
                return $this->helper('admin')->denyRequest();
            }

            $region = array_merge([
                'sortable' => false,
                'color' => '',
                'icon' => '',
                'description' => ''
            ], $region);

            return $this->render('regions:views/form.php', compact('region'));
        }

        return false;
    }

    public function remove_region($region) {

        $region = $this->module('regions')->region($region);

        if (!$region) {
            return false;
        }

        if (!$this->module('regions')->hasaccess($region['name'], 'delete')) {
            return $this->helper('admin')->denyRequest();
        }

        $this->module('regions')->removeRegion($region['name']);

        return '{"success":true}';
    }

    public function update_region($region) {

        $region = $this->module('regions')->region($region);

        if (!$region) {
            return false;
        }

        if (!$this->module('regions')->hasaccess($region['name'], 'form')) {
            return $this->helper('admin')->denyRequest();
        }

        if (!$this->param('data')) {
            return false;
        }

        $region = $this->module('regions')->updateRegion($region['name'], ['data' => $this->param('data')]);

        return $region;
    }
}
