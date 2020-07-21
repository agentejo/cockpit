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

use ArrayObject;

class Assets extends \Cockpit\AuthController {

    public function index() {

        return $this->render('cockpit:views/assets/index.php');
    }

    public function listAssets() {

        \session_write_close();

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
        $options = [
            'filter' => ['_p' => $this->param('folder', '')],
            'sort' => ['name' => 1]
        ];
        $this->app->trigger('cockpit.assetsfolders.find.before', [&$options]);
        $ret['folders'] = $this->app->storage->find('cockpit/assets_folders', $options)->toArray();

        return $ret;
    }

    public function asset($id) {
  
        return $this->app->storage->findOne('cockpit/assets', ['_id' => $id]);
    }

    public function upload() {

        \session_write_close();

        $meta = ['folder' => $this->param('folder', '')];

        return $this->module('cockpit')->uploadAssets('files', $meta);
    }

    public function uploadfolder() {

        \session_write_close();

        $paths = $this->param('paths') ?? [];
        $root = $this->param('folder');
        $files = $_FILES['files'] ?? [];

        if (!$paths) {
            return false;
        }

        $user = $this->module('cockpit')->getUser();
        $cache = new \ArrayObject([]);

        $mkdir = function($path) use($root, $cache, $user) {

            $folders = explode('/', $path);
            $i = 0;
            $_path = [];
            $folderId = null;
            $parentId = $root;

            while ($i < count($folders)) {
                $folder = $folders[$i];
                $_path[] = $folder;
                $_cpath = implode('/', $_path);

                if (!isset($cache[$_cpath])) {

                    $exists = $this->app->storage->findOne('cockpit/assets_folders', [
                        '_p' => $parentId, 
                        'name' => basename($_cpath)
                    ]);

                    if ($exists) {
                        $cache[$_cpath] = $exists['_id'];
                    } else {

                        $f = [
                            'name' => basename($_cpath),
                            '_p' => $parentId,
                            '_by' => $user['_id'],
                        ];
                
                        $this->app->storage->save('cockpit/assets_folders', $f);
                        $cache[$_cpath] = $f['_id'];
                    }

                }

                $folderId = $cache[$_cpath];
                $parentId = $folderId;

                $i++;
            }

            $cache[$path] = $folderId;

            return $folderId;
        };

        $mkdir = $mkdir->bindTo($this, $this);
        $folders = [];

        for ($i = 0; $i < count($files['name']); $i++) {

            $path = str_replace('\\', '/', dirname($paths[$i]));

            if (!isset($cache[$path])) {
                $mkdir($path);
            }

            if (!isset($folders[$path])) {
                
                $folders[$path] = [
                    'name' => [],
                    'error' => [],
                    'tmp_name' => [],
                ];
            }

            $folders[$path]['name'][] = $files['name'][$i];
            $folders[$path]['error'][] = $files['error'][$i];
            $folders[$path]['tmp_name'][] = $files['tmp_name'][$i];

        }

        $ret = [];

        foreach ($folders as $path => $_files) {
            $meta = ['folder' => $cache[$path]];
            $ret = \array_merge_recursive($ret, $this->module('cockpit')->uploadAssets($_files, $meta));
        } 

        return $ret;
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

        $user = $this->module('cockpit')->getUser();

        $folder = [
            'name' => $name,
            '_p' => $parent,
            '_by' => $user['_id'],
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

        $options = ['sort' => ['name' => 1]];

        $this->app->trigger('cockpit.assetsfolders.find.before', [&$options]);

        $_folders = $this->app->storage->find('cockpit/assets_folders', $options)->toArray();
        $folders = parent_sort($_folders);

        return $folders;
    }

}
