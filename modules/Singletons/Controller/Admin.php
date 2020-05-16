<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Singletons\Controller;


class Admin extends \Cockpit\AuthController {

    public function index() {

        $_singletons = $this->module('singletons')->getSingletonsInGroup();
        $singletons  = [];

        foreach ($_singletons as $name => $meta) {

            $meta['allowed'] = [
                'delete' => $this->module('cockpit')->hasaccess('singletons', 'delete'),
                'create' => $this->module('cockpit')->hasaccess('singletons', 'create'),
                'singleton_edit' => $this->module('singletons')->hasaccess($name, 'edit'),
                'singleton_form' => $this->module('singletons')->hasaccess($name, 'form')
            ];

            $singletons[] = [
              'name'  => $name,
              'label' => isset($meta['label']) && $meta['label'] ? $meta['label'] : $name,
              'meta'  => $meta
            ];
        }

        // sort singletons
        usort($singletons, function($a, $b) {
            return mb_strtolower($a['label']) <=> mb_strtolower($b['label']);
        });

        return $this->render('singletons:views/index.php', compact('singletons'));
    }

    public function singleton($name = null) {

        if ($name && !$this->module('singletons')->hasaccess($name, 'edit')) {
            return $this->helper('admin')->denyRequest();
        }

        if (!$name && !$this->module('cockpit')->hasaccess('singletons', 'create')) {
            return $this->helper('admin')->denyRequest();
        }

        $singleton = [ 'name'=>'', 'description' => '', 'fields'=>[], 'data' => null];

        if ($name) {

            $singleton = $this->module('singletons')->singleton($name);

            if (!$singleton) {
                return false;
            }

            if (!$this->app->helper('admin')->isResourceEditableByCurrentUser($singleton['_id'], $meta)) {
                return $this->render('cockpit:views/base/locked.php', compact('meta'));
            }

            $this->app->helper('admin')->lockResourceId($singleton['_id']);
        }

        // acl groups
        $aclgroups = [];

        foreach ($this->app->helper('acl')->getGroups() as $group => $superAdmin) {

            if (!$superAdmin) $aclgroups[] = $group;
        }

        return $this->render('singletons:views/singleton.php', compact('singleton', 'aclgroups'));
    }

    public function form($name = null) {

        if (!$name) {
            return false;
        }

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

        $this->app->helper('admin')->favicon = [
            'path' => 'singletons:icon.svg',
            'color' => $singleton['color']
        ];

        $lockId = "singleton_{$singleton['name']}";

        if (!$this->app->helper('admin')->isResourceEditableByCurrentUser($lockId, $meta)) {
            return $this->render('singletons:views/locked.php', compact('meta', 'singleton'));
        }

        $data = $this->module('singletons')->getData($name);

        $this->app->helper('admin')->lockResourceId($lockId);

        return $this->render('singletons:views/form.php', compact('singleton', 'data'));
        
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

        return ['success' => true];
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

        $lockId = "singleton_{$singleton['name']}";

        if (!$this->app->helper('admin')->isResourceEditableByCurrentUser($lockId)) {
            $this->stop(['error' => "Saving failed! Singleton is locked!"], 412);
        }

        $data['_mby'] = $this->module('cockpit')->getUser('_id');

        if (isset($data['_by'])) {
            $_data = $this->module('singletons')->getData($singleton['name']);
            $revision = !(json_encode($_data) == json_encode($data));
        } else {
            $data['_by'] = $data['_mby'];
            $revision = true;
        }

        $data = $this->module('singletons')->saveData($singleton['name'], $data, ['revision' => $revision]);

        $this->app->helper('admin')->lockResourceId($lockId);

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
