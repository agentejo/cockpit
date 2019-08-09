<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Collections\Controller;


class Trash extends \Cockpit\AuthController {

    public function collection($name = null) {

        if (!$name) {
            return null;
        }

        $collection = $this->module('collections')->collection($name);

        if (!$collection) return false;

        if (!$this->module('collections')->hasaccess($collection['name'], 'entries_delete')) {
            return $this->helper('admin')->denyRequest();
        }

        return $this->render('collections:views/trash/collection.php', compact('collection'));
    }

    public function find() {

        \session_write_close();

        $collection = $this->app->param('collection');
        $options    = $this->app->param('options');

        if (!$collection) return false;

        $collection = $this->app->module('collections')->collection($collection);

        if (!$collection) return false;

        $options['sort'] = ['_created' => -1];

        $options['filter'] = [
            'collection' => $collection['name']
        ];

        $entries = $this->app->storage->find('collections/_trash', $options)->toArray();

        $count = $this->app->storage->count('collections/_trash', $options['filter']);
        $pages = isset($options['limit']) ? ceil($count / $options['limit']) : 1;
        $page  = 1;

        if ($pages > 1 && isset($options['skip'])) {
            $page = ceil($options['skip'] / $options['limit']) + 1;
        }

        return compact('entries', 'count', 'pages', 'page');
    }

    public function empty($collection) {

        $collection = $this->app->module('collections')->collection($collection);

        if (!$collection) return false;

        if (!$this->module('collections')->hasaccess($collection['name'], 'entries_delete')) {
            return $this->helper('admin')->denyRequest();
        }

        $filter = [
            'collection' => $collection['name']
        ];

        $this->app->storage->remove('collections/_trash', $filter);

        return ['success' => true];
    }

    public function delete($collection) {

        $collection = $this->module('collections')->collection($collection);

        if (!$collection) {
            return false;
        }

        if (!$this->module('collections')->hasaccess($collection['name'], 'entries_delete')) {
            return $this->helper('admin')->denyRequest();
        }

        $filter = $this->param('filter', false);
        
        if (!$filter) {
            return false;
        }
        
        $filter['collection'] = $collection['name'];

        $this->app->storage->remove('collections/_trash', $filter);

        return true;
    }

    public function recycle($collection) {

        $collection = $this->module('collections')->collection($collection);

        if (!$collection) {
            return false;
        }

        if (!$this->module('collections')->hasaccess($collection['name'], 'entries_delete')) {
            return $this->helper('admin')->denyRequest();
        }

        $filter = $this->param('filter', false);

        if (!$filter) {
            return false;
        }

        $filter['collection'] = $collection['name'];

        $items = $this->app->storage->find('collections/_trash', ['filter' => $filter])->toArray();

        foreach ($items as $item) {
            $this->app->storage->insert("collections/{$collection['_id']}", $item['data']);
        }

        $this->app->storage->remove('collections/_trash', $filter);

        return true;
    }
}