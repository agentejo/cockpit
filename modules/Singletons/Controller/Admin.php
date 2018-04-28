<?php

namespace Singletons\Controller;


class Admin extends \Cockpit\AuthController {

    public function index() {

        $singletons = $this->module('singletons')->getSingletonsInGroup();

        foreach ($singletons as $name => $meta) {

            $singletons[$name]['allowed'] = [
                'delete' => $this->module('cockpit')->hasaccess('singletons', 'delete'),
                'create' => $this->module('cockpit')->hasaccess('singletons', 'create'),
                'singleton_edit' => $this->module('singletons')->hasaccess($name, 'edit'),
                'singleton_form' => $this->module('singletons')->hasaccess($name, 'form')
            ];
        }

        return $this->render('singletons:views/index.php', compact('singletons'));
    }

    public function singleton($name = null) {

        if ($name && !$this->module('singletons')->hasaccess($name, 'edit')) {
            return $this->helper('admin')->denyRequest();
        }

        if (!$name && !$this->module('cockpit')->hasaccess('singletons', 'create')) {
            return $this->helper('admin')->denyRequest();
        }

        $singleton = [ 'name'=>'', 'description' => '', 'fields'=>[], 'template' => '', 'data' => null];

        if ($name) {

            $singleton = $this->module('singletons')->singleton($name);

            if (!$singleton) {
                return false;
            }
        }

        // acl groups
        $aclgroups = [];

        foreach ($this->app->helper("acl")->getGroups() as $group => $superAdmin) {

            if (!$superAdmin) $aclgroups[] = $group;
        }

        return $this->render('singletons:views/singleton.php', compact('singleton', 'aclgroups'));
    }

    public function form($name = null) {

        if ($name) {

            $singleton = $this->module('singletons')->singleton($name);

            if (!$singleton) {
                return false;
            }

            if (!$this->module('singletons')->hasaccess($singleton['name'], 'form')) {
                return $this->helper('admin')->denyRequest();
            }

            $singleton = array_merge([
                'sortable' => false,
                'color' => '',
                'icon' => '',
                'description' => ''
            ], $singleton);

            $data = $this->module('singletons')->getData($name);

            return $this->render('singletons:views/form.php', compact('singleton', 'data'));
        }

        return false;
    }

    public function remove_singleton($singleton) {

        $singleton = $this->module('singletons')->singleton($singleton);

        if (!$singleton) {
            return false;
        }

        if (!$this->module('singletons')->hasaccess($singleton['name'], 'delete')) {
            return $this->helper('admin')->denyRequest();
        }

        $this->module('singletons')->removeSingleton($singleton['name']);

        return '{"success":true}';
    }

    public function update_data($singleton) {

        $singleton = $this->module('singletons')->singleton($singleton);
        $data = $this->param('data');

        if (!$singleton || !$data) {
            return false;
        }

        if (!$this->module('singletons')->hasaccess($singleton['name'], 'form')) {
            return $this->helper('admin')->denyRequest();
        }

        $data['_mby'] = $this->module('cockpit')->getUser('_id');

        if (isset($data['_by'])) {
            $_data = $this->module('singletons')->getData($singleton['name']);
            $revision = !(json_encode($_data) == json_encode($data));
        } else {
            $data['_by'] = $data['_mby'];
            $revision = true;
        }

        $singleton = $this->module('singletons')->saveData($singleton['name'], $data, ['revision' => $revision]);

        return ['data' => $data];
    }

    public function revisions($singleton, $id) {

        if (!$this->module('singletons')->hasaccess($singleton, 'form')) {
            return $this->helper('admin')->denyRequest();
        }

        $singleton = $this->module('singletons')->singleton($singleton);

        if (!$singleton) {
            return false;
        }

        $data = $this->app->storage->getKey('singletons', $singleton['name']);

        if (!$data) {
            return false;
        }

        $revisions = $this->app->helper('revisions')->getList($id);

        return $this->render('singletons:views/revisions.php', compact('singleton', 'data', 'revisions', 'id'));
    }
}
