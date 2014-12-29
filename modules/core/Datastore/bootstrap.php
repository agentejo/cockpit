<?php


$tables = []; # cached tables

$this->module("datastore")->extend([

    "get_datastore" => function($name) use($app, $tables) {

        if (!isset($tables[$name])) {
            $tables[$name] = $app->db->findOne("common/datastore", ["name"=>$name]);
        }

        return $tables[$name];
    },

    "collectionById" => function($datastoreId) use($app) {

        $entrydb = "datastore{$datastoreId}";

        return $app->db->getCollection("datastore/{$entrydb}");
    },

    "entries" => function($name) use($app) {

        $datastore = $this->get_datastore($name);

        if (!$datastore) return false;

        $entrydb = "datastore".$datastore["_id"];

        return $app->db->getCollection("datastore/{$entrydb}");
    },

    "find" => function($table, $options = []) {

        $datastore = $this->get_datastore($table);

        if (!$datastore) return false;

        $collection = "datastore".$datastore["_id"];

        return $this->app->db->find("datastore/{$collection}", $options);
    },

    "findOne" => function($table, $criteria = [], $projection = null) {

        $datastore = $this->get_datastore($table);

        if (!$datastore) return false;

        $collection = "datastore".$datastore["_id"];

        return $this->app->db->findOne("datastore/{$collection}", $criteria, $projection);
    },

    "save_entry" => function($table, $data) {

        $datastore = $this->get_datastore($table);

        if (!$datastore) return false;

        $collection = "datastore".$datastore["_id"];

        $data["modified"] = time();

        if (!isset($data["_id"])){
            $data["created"] = $datastore["modified"];
        }

        return $this->app->db->save("datastore/{$collection}", $data);
    },

    "remove" => function($table, $criteria) {

        $datastore = $this->get_datastore($table);

        if (!$datastore) return false;

        $collection = "datastore".$datastore["_id"];

        return $this->app->db->remove("datastore/{$collection}", $criteria);
    },

    'count' => function($table, $criteria = []) {

        $datastore = $this->get_datastore($table);

        if (!$datastore) return false;

        $collection = "datastore".$datastore["_id"];

        return $this->app->db->count("datastore/{$collection}", $criteria);
    }
]);



// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_REST) include_once(__DIR__.'/admin.php');
