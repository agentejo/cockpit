<?php
namespace Collections\Controller;

class RestApi extends \LimeExtra\Controller {

    protected function before() {
        $this->app->response->mime = 'json';
    }

    public function get($collection=null) {

        if (!$collection) {
            return $this->stop('{"error": "Missing collection name"}', 412);
        }

        if (!$this->module('collections')->exists($collection)) {
            return $this->stop('{"error": "Collection not found"}', 412);
        }

        $collection = $this->module('collections')->collection($collection);
        $user = $this->module('cockpit')->getUser();

        if ($user) {

            if (!$this->module('collections')->hasaccess($collection['name'], 'entries_view')) {
                return $this->stop('{"error": "Unauthorized"}', 401);
            }
        }

        $options = [];

        if ($filter   = $this->param('filter', null))   $options['filter'] = $filter;
        if ($limit    = $this->param('limit', null))    $options['limit'] = intval($limit);
        if ($sort     = $this->param('sort', null))     $options['sort'] = $sort;
        if ($fields   = $this->param('fields', null))   $options['fields'] = $fields;
        if ($skip     = $this->param('skip', null))     $options['skip'] = intval($skip);
        if ($populate = $this->param('populate', null)) $options['populate'] = $populate;

        // cast string values if get request
        if ($filter && isset($_GET['filter'])) {
            $options['filter'] = $this->_fixStringBooleanNumericValues($filter);
        }

        // fields filter
        $fieldsFilter = [];

        if ($fieldsFilter = $this->param('fieldsFilter', [])) $options['fieldsFilter'] = $fieldsFilter;
        if ($lang = $this->param('lang', false)) $fieldsFilter['lang'] = $lang;
        if ($ignoreDefaultFallback = $this->param('ignoreDefaultFallback', false)) $fieldsFilter['ignoreDefaultFallback'] = $ignoreDefaultFallback;
        if ($user) $fieldsFilter["user"] = $user;

        if (is_array($fieldsFilter) && count($fieldsFilter)) {
            $options['fieldsFilter'] = $fieldsFilter;
        }

        if (isset($options["sort"])) {

            foreach ($sort as $key => &$value) {
                $options["sort"][$key]= intval($value);
            }
        }

        $entries = $this->module('collections')->find($collection['name'], $options);

        // return only entries array - due to legacy
        if ((boolean) $this->param('simple', false)) {
            return $entries;
        }

        $fields = [];

        foreach ($collection['fields'] as $field) {

            if (
                $user && isset($field['acl']) &&
                is_array($field['acl']) && count($field['acl']) &&
                !(in_array($user['_id'] , $field['acl']) || in_array($user['group'] , $field['acl']))
            ) {
                continue;
            }

            $fields[$field['name']] = [
                'name' => $field['name'],
                'type' => $field['type'],
                'localize' => $field['localize'],
                'options' => $field['options'],
            ];
        }

        return [
            'fields'   => $fields,
            'entries'  => $entries,
            'total'    => (!$skip && !$limit) ? count($entries) : $this->module('collections')->count($collection['name'], $filter ? $filter : [])
        ];

        return $entries;
    }

    public function save($collection=null) {

        $user = $this->module('cockpit')->getUser();
        $data = $this->param('data', null);

        if (!$collection || !$data) {
            return false;
        }

        if (!$this->module('collections')->exists($collection)) {
            return $this->stop('{"error": "Collection not found"}', 412);
        }

        if ($user && !$this->module('collections')->hasaccess($collection, isset($data['_id']) ? 'entries_edit':'entries_create')) {
            return $this->stop('{"error": "Unauthorized"}', 401);
        }

        $data['_by'] = $this->module('cockpit')->getUser('_id');

        $data = $this->module('collections')->save($collection, $data);

        return $data;
    }

    public function remove($collection=null) {

        $user   = $this->module('cockpit')->getUser();
        $filter = $this->param('filter', null);
        $count  = $this->param('count', false);

        if (!$collection || !$filter) {
            return $this->stop('{"error": "Please provide a collection name and filter"}', 417);
        }

        // handele single item cases
        if (is_string($filter)) {
            $filter = ['_id' => $filter];
        } elseif (isset($filter['_id'])) {
            $filter = ['_id' => $filter['_id']];
        }

        if (!$this->module('collections')->exists($collection)) {
            return $this->stop('{"error": "Collection not found"}', 412);
        }

        if ($user && !$this->module('collections')->hasaccess($collection, 'entries_delete')) {
            return $this->stop('{"error": "Unauthorized"}', 401);
        }

        if ($count) {
            $count = $this->module('collections')->count($collection, $filter);
        }

        $this->module('collections')->remove($collection, $filter);

        return ['success' => true, 'count' => $count];
    }

    public function createCollection() {

        $user = $this->module('cockpit')->getUser();
        $name = $this->param('name', null);
        $data = $this->param('data', null);

        if (!$name || !$data) {
            return false;
        }

        if ($user && !$this->module('cockpit')->isSuperAdmin()) {
            return $this->stop('{"error": "Unauthorized"}', 401);
        }

        $collection = $this->module('collections')->createCollection($name, $data);

        return $collection;
    }

    public function updateCollection($name = null) {

        $user = $this->module('cockpit')->getUser();
        $data = $this->param('data', null);

        if (!$name || !$data) {
            return false;
        }

        $collection = $this->module('collections')->collection($name);

        if ($user && !$this->module('collections')->hasaccess($collection, 'collection_edit')) {
            return $this->stop('{"error": "Unauthorized"}', 401);
        }

        $collection = $this->module('collections')->updateCollection($name, $data);

        return $collection;
    }

    public function collection($name) {

        $user = $this->module('cockpit')->getUser();

        if ($user) {
            $collections = $this->module("collections")->getCollectionsInGroup($user['group'], true);
        } else {
            $collections = $this->module("collections")->collections(true);
        }

        if (!isset($collections[$name])) {
           return $this->stop('{"error": "Collection not found"}', 412);
        }

        return $collections[$name];
    }

    public function listCollections($extended = false) {

        $user = $this->module('cockpit')->getUser();

        if ($user) {
            $collections = $this->module("collections")->getCollectionsInGroup($user['group'], $extended);
        } else {
            $collections = $this->module('collections')->collections($extended);
        }

        return $extended ? $collections : array_keys($collections);
    }

    protected function _fixStringBooleanNumericValues(&$array) {

        if (!is_array($array)) {
            return $array;
        }

        foreach ($array as $k => $v) {

            if (is_array($array[$k])) {
                $array[$k] = $this->_fixStringBooleanNumericValues($array[$k]);
            }

            if (is_string($v)) {

                if ($v === 'true' || $v === 'false') {
                    $v = filter_var($v, FILTER_VALIDATE_BOOLEAN);
                } elseif(is_numeric($v)) {
                    $v = $v + 0;
                }
            }

            $array[$k] = $v;
        }

        return $array;
    }
}
