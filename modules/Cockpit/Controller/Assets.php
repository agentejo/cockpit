<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cockpit\Controller;

class Assets extends \Cockpit\AuthController {

    public function index() {

        return $this->render('cockpit:views/assets/index.php');
    }

    public function listAssets() {

        $options = [
            'sort' => ['created' => -1]
        ];

        if ($filter = $this->param('filter', null)) $options['filter'] = $filter;
        if ($limit  = $this->param('limit' , null)) $options['limit']  = $limit;
        if ($sort   = $this->param('sort'  , null)) $options['sort']   = $sort;
        if ($skip   = $this->param('skip'  , null)) $options['skip']   = $skip;
        if ($folder = $this->param('folder'  , '')) $options['folder'] = $folder;

        $ret = $this->module('cockpit')->listAssets($options);

        // virtual folders
        $ret['folders'] = $this->app->storage->find('cockpit/assets_folders', [
            'filter' => ['_p' => $this->param('folder', '')],
            'sort' => ['name' => 1]
        ])->toArray();

        return $ret;
    }

    public function asset($id) {
  
        return $this->app->storage->findOne('cockpit/assets', ['_id' => $id]);
    }

    public function upload() {

        $meta = ['folder' => $this->param('folder', '')];

        return $this->module('cockpit')->uploadAssets('files', $meta);
    }

    public function removeAssets() {

        if ($assets = $this->param('assets', false)) {
            return $this->module('cockpit')->removeAssets($assets);
        }

        return false;
    }

    public function updateAsset() {

        if ($asset = $this->param('asset', false)) {
            return $this->module('cockpit')->updateAssets($asset);
        }

        return false;
    }

    public function addFolder() {

        $name   = $this->param('name', null);
        $parent = $this->param('parent', '');

        if (!$name) return;

        $folder = [
            'name' => $name,
            '_p' => $parent
        ];

        $this->app->storage->save('cockpit/assets_folders', $folder);

        return $folder;
    }

    public function renameFolder() {

        $folder = $this->param('folder');
        $name = $this->param('name');

        if (!$folder || !$name) {
            return false;
        }

        $folder['name'] = $name;

        $this->app->storage->save('cockpit/assets_folders', $folder);

        return $folder;
    }

    public function removeFolder() {

        $folder = $this->param('folder');

        if (!$folder || !isset($folder['_id'])) {
            return false;
        }

        $ids = [$folder['_id']];
        $f   = ['_id' => $folder['_id']];

        while ($f = $this->app->storage->findOne('cockpit/assets_folders', ['_p' => $f['_id']])) {
            $ids[] = $f['_id'];
        }

        $this->app->storage->remove('cockpit/assets_folders', ['_id' => ['$in' => $ids]]);

        return $ids;
    }

    public function _folders() {

        function parent_sort(array $objects, array &$result=[], $parent='', $depth=0) {

            foreach ($objects as $key => $object) {

                if ($object['_p'] == $parent) {
                    $object['_lvl'] = $depth;
                    array_push($result, $object);
                    unset($objects[$key]);
                    parent_sort($objects, $result, $object['_id'], $depth + 1);
                }
            }
            return $result;
        }

        $_folders = $this->app->storage->find('cockpit/assets_folders', [
            'sort' => ['name' => 1]
        ])->toArray();

        $folders = parent_sort($_folders);

        return $folders;
    }

}
