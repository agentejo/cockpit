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
        if ($skip     = $this->param('skip', null))     $options['skip'] = intval($skip);
        if ($populate = $this->param('populate', null)) $options['populate'] = $populate;
        if ($lang = $this->param('lang', null)) $options['lang'] = $lang;
        if ($user) $options["user"] = $user;

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

        if (!$this->module('collections')->hasaccess($collection, isset($data['_id']) ? 'entries_create':'entries_edit')) {
            return $this->stop('{"error": "Unauthorized"}', 401);
        }

        $data = $this->module('collections')->save($collection, $data);

        return $data;
    }

    public function createCollection() {

        $user = $this->module('cockpit')->getUser();
        $name = $this->param('name', null);
        $data = $this->param('data', null);

        if (!$name || !$data || !$user) {
            return false;
        }

        if (!$this->module('cockpit')->isSuperAdmin()) {
            return $this->stop('{"error": "Unauthorized"}', 401);
        }

        $collection = $this->module('collections')->createCollection($name, $data);

        return $collection;
    }

    public function updateCollection() {

        $user = $this->module('cockpit')->getUser();
        $name = $this->param('name', null);
        $data = $this->param('data', null);

        if (!$name || !$data || !$user) {
            return false;
        }

        $collection = $this->module('collections')->collection($name);

        if (!$this->module('cockpit')->isSuperAdmin()) {
            return $this->stop('{"error": "Unauthorized"}', 401);
        }

        $collection = $this->module('collections')->updateCollection($name, $data);

        return $collection;
    }

    public function collection($name) {

        $user = $this->module('cockpit')->getUser();

        if (!$name || !$user) {
            return $user ? $this->stop('{"error": "Unauthorized"}', 401) : false;
        }

        $collections = $this->module("collections")->getCollectionsInGroup($user['group'], true);

        if (!isset($collections[$name])) {
           return $this->stop('{"error": "Collection not found"}', 412);
        }

        return $collections[$name];
    }


}
