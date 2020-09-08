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


class Admin extends \Cockpit\AuthController {


    public function index() {

        $_collections = $this->module('collections')->getCollectionsInGroup(null, false);
        $collections  = [];

        foreach ($_collections as $collection => $meta) {

            $meta['allowed'] = [
                'delete' => $this->module('cockpit')->hasaccess('collections', 'delete'),
                'create' => $this->module('cockpit')->hasaccess('collections', 'create'),
                'edit' => $this->module('collections')->hasaccess($collection, 'collection_edit'),
                'entries_create' => $this->module('collections')->hasaccess($collection, 'collection_create'),
                'entries_delete' => $this->module('collections')->hasaccess($collection, 'entries_delete'),
            ];

            $meta['itemsCount'] = null;

            $collections[] = [
                'name' => $collection,
                'label' => isset($meta['label']) && $meta['label'] ? $meta['label'] : $collection,
                'meta' => $meta
            ];
        }

        // sort collections
        usort($collections, function($a, $b) {
            return mb_strtolower($a['label']) <=> mb_strtolower($b['label']);
        });

        return $this->render('collections:views/index.php', compact('collections'));
    }

    public function _collections() {
        return $this->module('collections')->collections();
    }

    public function _find() {

        if ($this->param('collection') && $this->param('options')) {
            return $this->module('collections')->find($this->param('collection'), $this->param('options'));
        }

        return false;
    }

    public function collection($name = null) {

        if ($name && !$this->module('collections')->hasaccess($name, 'collection_edit')) {
            return $this->helper('admin')->denyRequest();
        }

        if (!$name && !$this->module('cockpit')->hasaccess('collections', 'create')) {
            return $this->helper('admin')->denyRequest();
        }

        $default = [
            'name' => '',
            'label' => '',
            'color' => '',
            'fields'=>[],
            'acl' => new \ArrayObject,
            'sortable' => false,
            'sort' => [
                'column' => '_created',
                'dir' => -1,
            ],
            'in_menu' => false
        ];

        $collection = $default;

        if ($name) {

            $collection = $this->module('collections')->collection($name);

            if (!$collection) {
                return false;
            }

            if (!$this->app->helper('admin')->isResourceEditableByCurrentUser($collection['_id'], $meta)) {
                return $this->render('cockpit:views/base/locked.php', compact('meta'));
            }

            $this->app->helper('admin')->lockResourceId($collection['_id']);

            $collection = array_merge($default, $collection);
        }

        // get field templates
        $templates = [];

        foreach ($this->app->helper('fs')->ls('*.php', 'collections:fields-templates') as $file) {
            $templates[] = include($file->getRealPath());
        }

        foreach ($this->app->module('collections')->collections() as $col) {
            $templates[] = $col;
        }

        // acl groups
        $aclgroups = [];

        foreach ($this->app->helper('acl')->getGroups() as $group => $superAdmin) {

            if (!$superAdmin) $aclgroups[] = $group;
        }

        // rules
        $rules = [
            'create' => !$name ? "<?php\n\n" : $this->app->helper('fs')->read("#storage:collections/rules/{$name}.create.php"),
            'read'   => !$name ? "<?php\n\n" : $this->app->helper('fs')->read("#storage:collections/rules/{$name}.read.php"),
            'update' => !$name ? "<?php\n\n" : $this->app->helper('fs')->read("#storage:collections/rules/{$name}.update.php"),
            'delete' => !$name ? "<?php\n\n" : $this->app->helper('fs')->read("#storage:collections/rules/{$name}.delete.php"),
        ];

        return $this->render('collections:views/collection.php', compact('collection', 'templates', 'aclgroups', 'rules'));
    }

    public function save_collection() {

        $collection = $this->param('collection');
        $rules      = $this->param('rules', null);

        if (!$collection) {
            return false;
        }

        $isUpdate = isset($collection['_id']);

        if (!$isUpdate && !$this->module('cockpit')->hasaccess('collections', 'create')) {
            return $this->helper('admin')->denyRequest();
        }

        if ($isUpdate && !$this->module('collections')->hasaccess($collection['name'], 'collection_edit')) {
            return $this->helper('admin')->denyRequest();
        }

        if ($isUpdate && !$this->app->helper('admin')->isResourceEditableByCurrentUser($collection['_id'])) {
            $this->stop(['error' => "Saving failed! Collection is locked!"], 412);
        }

        $collection = $this->module('collections')->saveCollection($collection['name'], $collection, $rules);

        if (!$isUpdate) {
            $this->app->helper('admin')->lockResourceId($collection['_id']);
        }

        return $collection;
    }

    public function entries($collection) {

        if (!$this->module('collections')->hasaccess($collection, 'entries_view')) {
            return $this->helper('admin')->denyRequest();
        }

        $collection = $this->module('collections')->collection($collection);

        if (!$collection) {
            return false;
        }

        $collection = array_merge([
            'sortable' => false,
            'sort' => [
                'column' => '_created',
                'dir' => -1,
            ],
            'color' => '',
            'icon' => '',
            'description' => ''
        ], $collection);

        $context = _check_collection_rule($collection, 'read', ['options' => ['filter'=>[]]]);

        $this->app->helper('admin')->favicon = [
            'path' => 'collections:icon.svg',
            'color' => $collection['color']
        ];

        if ($context && isset($context->options['fields'])) {
            foreach ($collection['fields'] as &$field) {
                if (isset($context->options['fields'][$field['name']]) && !$context->options['fields'][$field['name']]) {
                    $field['lst'] = false;
                }
            }
        }

        $view = 'collections:views/entries.php';

        if ($override = $this->app->path('#config:collections/'.$collection['name'].'/views/entries.php')) {
            $view = $override;
        }

        return $this->render($view, compact('collection'));
    }

    public function entry($collection, $id = null) {

        if ($id && !$this->module('collections')->hasaccess($collection, 'entries_view')) {
            return $this->helper('admin')->denyRequest();
        }

        if (!$id && !$this->module('collections')->hasaccess($collection, 'entries_create')) {
            return $this->helper('admin')->denyRequest();
        }

        $collection    = $this->module('collections')->collection($collection);
        $entry         = new \ArrayObject([]);
        $excludeFields = [];

        if (!$collection) {
            return false;
        }

        $collection = array_merge([
            'sortable' => false,
            'sort' => [
                'column' => '_created',
                'dir' => -1,
            ],
            'color' => '',
            'icon' => '',
            'description' => ''
        ], $collection);

        $this->app->helper('admin')->favicon = [
            'path' => 'collections:icon.svg',
            'color' => $collection['color']
        ];

        if ($id) {

            $entry = $this->module('collections')->findOne($collection['name'], ['_id' => $id]);
            //$entry = $this->app->storage->findOne("collections/{$collection['_id']}", ['_id' => $id]);

            if (!$entry) {
                return cockpit()->helper('admin')->denyRequest();
            }

            if (!$this->app->helper('admin')->isResourceEditableByCurrentUser($id, $meta)) {
                return $this->render('collections:views/locked.php', compact('meta', 'collection', 'entry'));
            }

            $this->app->helper('admin')->lockResourceId($id);
        }

        $context = _check_collection_rule($collection, 'read', ['options' => ['filter'=>[]]]);

        if ($context && isset($context->options['fields'])) {
            foreach ($context->options['fields'] as $field => $include) {
                if(!$include) $excludeFields[] = $field;
            }
        }

        $view = 'collections:views/entry.php';

        if ($override = $this->app->path('#config:collections/'.$collection['name'].'/views/entry.php')) {
            $view = $override;
        }

        return $this->render($view, compact('collection', 'entry', 'excludeFields'));
    }

    public function save_entry($collection) {

        $collection = $this->module('collections')->collection($collection);

        if (!$collection) {
            return false;
        }

        $entry = $this->param('entry', false);

        if (!$entry) {
            return false;
        }

        if (!isset($entry['_id']) && !$this->module('collections')->hasaccess($collection['name'], 'entries_create')) {
            return $this->helper('admin')->denyRequest();
        }

        if (isset($entry['_id']) && !$this->module('collections')->hasaccess($collection['name'], 'entries_edit')) {
            return $this->helper('admin')->denyRequest();
        }

        $entry['_mby'] = $this->module('cockpit')->getUser('_id');

        if (isset($entry['_id'])) {

            if (!$this->app->helper('admin')->isResourceEditableByCurrentUser($entry['_id'])) {
                $this->stop(['error' => "Saving failed! Entry is locked!"], 412);
            }

            $_entry = $this->module('collections')->findOne($collection['name'], ['_id' => $entry['_id']]);
            $revision = !(json_encode($_entry) == json_encode($entry));

        } else {

            $entry['_by'] = $entry['_mby'];
            $revision = true;

            if ($collection['sortable']) {
                 $entry['_o'] = $this->app->storage->count("collections/{$collection['_id']}", ['_pid' => ['$exists' => false]]);
            }

        }

        try {
            $entry = $this->module('collections')->save($collection['name'], $entry, ['revision' => $revision]);
        } catch(\Throwable $e) {
            $this->app->stop(['error' => $e->getMessage()], 412);
        }

        $this->app->helper('admin')->lockResourceId($entry['_id']);

        return $entry;
    }

    public function delete_entries($collection) {

        \session_write_close();

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

        $items = $this->module('collections')->find($collection['name'], ['filter' => $filter]);

        if (count($items)) {
            
            $trashItems = [];
            $time = time();
            $by = $this->module('cockpit')->getUser('_id');

            foreach ($items as $item) {

                $trashItems[] = [
                    'collection' => $collection['name'],
                    'data' => $item,
                    '_by' => $by,
                    '_created' => $time
                ];
            }

            $this->app->storage->getCollection('collections/_trash')->insertMany($trashItems);
        }

        $this->module('collections')->remove($collection['name'], $filter);

        return true;
    }

    public function update_order($collection) {

        \session_write_close();

        $collection = $this->module('collections')->collection($collection);
        $entries = $this->param('entries');

        if (!$collection) return false;
        if (!$entries) return false;

        $_collectionId = $collection['_id'];

        if (is_array($entries) && count($entries)) {

            foreach($entries as $entry) {
                $this->app->storage->save("collections/{$_collectionId}", $entry);
            }
        }

        return $entries;
    }

    public function export($collection) {

        \session_write_close();

        if (!$this->app->module("cockpit")->hasaccess('collections', 'manage')) {
            return false;
        }

        $collection = $this->module('collections')->collection($collection);

        if (!$collection) return false;

        if (!$this->module('collections')->hasaccess($collection['name'], 'entries_view')) {
            return $this->helper('admin')->denyRequest();
        }

        $entries = $this->module('collections')->find($collection['name']);

        return json_encode($entries, JSON_PRETTY_PRINT);
    }


    public function tree() {

        \session_write_close();

        $collection = $this->app->param('collection');

        if (!$collection) return false;

        $items = $this->app->module('collections')->find($collection);

        if (count($items)) {

            $items = $this->helper('utils')->buildTree($items, [
                'parent_id_column_name' => '_pid',
                'children_key_name' => 'children',
                'id_column_name' => '_id',
    			'sort_column_name' => '_o'
            ]);
        }

        return $items;
    }

    public function find() {

        \session_write_close();

        $collection = $this->app->param('collection');
        $options    = $this->app->param('options');

        if (!$collection) return false;

        $collection = $this->app->module('collections')->collection($collection);

        if (isset($options['filter']) && is_string($options['filter'])) {

            $filter = null;

            if (\preg_match('/^\{(.*)\}$/', $options['filter'])) {

                try {
                    $filter = json5_decode($options['filter'], true);
                } catch (\Exception $e) {}
            }

            if (!$filter) {
                $filter = $this->_filter($options['filter'], $collection, $options['lang'] ?? null);
            }

            $options['filter'] = $filter;
        }

        $this->app->trigger("collections.admin.find.before.{$collection['name']}", [&$options]);
        $entries = $this->app->module('collections')->find($collection['name'], $options);
        $this->app->trigger("collections.admin.find.after.{$collection['name']}", [&$entries, $options]);
        
        $count = $this->app->module('collections')->count($collection['name'], isset($options['filter']) ? $options['filter'] : []);
        $pages = isset($options['limit']) ? ceil($count / $options['limit']) : 1;
        $page  = 1;

        if ($pages > 1 && isset($options['skip'])) {
            $page = ceil($options['skip'] / $options['limit']) + 1;
        }

        return compact('entries', 'count', 'pages', 'page');
    }


    public function revisions($collection, $id) {

        if (!$this->module('collections')->hasaccess($collection, 'entries_edit')) {
            return $this->helper('admin')->denyRequest();
        }

        $collection = $this->module('collections')->collection($collection);

        if (!$collection) {
            return false;
        }

        $entry = $this->module('collections')->findOne($collection['name'], ['_id' => $id]);

        if (!$entry) {
            return false;
        }

        $revisions = $this->app->helper('revisions')->getList($id);


        return $this->render('collections:views/revisions.php', compact('collection', 'entry', 'revisions'));
    }

    protected function _filter($filter, $collection, $lang = null) {

        if ($this->app->storage->type == 'mongolite') {
            return $this->_filterLight($filter, $collection, $lang);
        }

        if ($this->app->storage->type == 'mongodb') {
            return $this->_filterMongo($filter, $collection, $lang);
        }

        return null;

    }

    protected function _filterLight($filter, $collection, $lang) {

        $allowedtypes = ['text','longtext','boolean','select','html','wysiwyg','markdown','code'];
        $criterias    = [];
        $_filter      = null;

        foreach ($collection['fields'] as $field) {

            $name = $field['name'];

            if ($lang && $field['localize']) {
                $name = "{$name}_{$lang}";
            }

            if ($field['type'] != 'boolean' && in_array($field['type'], $allowedtypes)) {
                $criteria = [];
                $criteria[$name] = ['$regex' => $filter];
                $criterias[] = $criteria;
            }

            if ($field['type']=='collectionlink') {
                $criteria = [];
                $criteria[$name.'.display'] = ['$regex' => $filter];
                $criterias[] = $criteria;
            }

            if ($field['type']=='location') {
                $criteria = [];
                $criteria[$name.'.address'] = ['$regex' => $filter];
                $criterias[] = $criteria;
            }

        }

        if (count($criterias)) {
            $_filter = ['$or' => $criterias];
        }

        return $_filter;
    }

    protected function _filterMongo($filter, $collection, $lang) {

        $allowedtypes = ['text','longtext','boolean','select','html','wysiwyg','markdown','code'];
        $criterias    = [];
        $_filter      = null;

        foreach ($collection['fields'] as $field) {

            $name = $field['name'];

            if ($lang && $field['localize']) {
                $name = "{$name}_{$lang}";
            }

            if ($field['type'] != 'boolean' && in_array($field['type'], $allowedtypes)) {
                $criteria = [];
                $criteria[$name] = ['$regex' => $filter, '$options' => 'i'];
                $criterias[] = $criteria;
            }

            if ($field['type']=='collectionlink') {
                $criteria = [];
                $criteria[$name.'.display'] = ['$regex' => $filter, '$options' => 'i'];
                $criterias[] = $criteria;
            }

            if ($field['type']=='location') {
                $criteria = [];
                $criteria[$name.'.address'] = ['$regex' => $filter, '$options' => 'i'];
                $criterias[] = $criteria;
            }

        }

        if (count($criterias)) {
            $_filter = ['$or' => $criterias];
        }

        return $_filter;
    }
}
