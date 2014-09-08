<?php

namespace Forms\Controller;

class Api extends \Cockpit\Controller {


    public function find(){

        $options = [];

        if ($filter = $this->param("filter", null)) $options["filter"] = $filter;
        if ($limit  = $this->param("limit", null))  $options["limit"] = $limit;
        if ($sort   = $this->param("sort", null))   $options["sort"] = $sort;
        if ($skip   = $this->param("skip", null))   $options["skip"] = $skip;

        $docs = $this->app->db->find("common/forms", $options);

        if (count($docs) && $this->param("extended", false)){
            foreach ($docs as &$doc) {
                $doc["count"] = $this->app->module("forms")->collectionById($doc["_id"])->count();
            }
        }

        return json_encode($docs->toArray());
    }

    public function findOne(){

        $doc = $this->app->db->findOne("common/forms", $this->param("filter", []));

        return $doc ? json_encode($doc) : '{}';
    }


    public function save(){

        $form = $this->param("form", null);

        if ($form) {

            $form["modified"] = time();
            $form["_uid"]     = @$this->user["_id"];

            if (!isset($form["_id"])){
                $form["created"] = $form["modified"];
            }

            $this->app->db->save("common/forms", $form);
        }

        return $form ? json_encode($form) : '{}';
    }

    public function remove(){

        $form = $this->param("form", null);

        if ($form) {
            $frm = "form".$form["_id"];

            $this->app->db->dropCollection("forms/{$frm}");
            $this->app->db->remove("common/forms", ["_id" => $form["_id"]]);
        }

        return $form ? '{"success":true}' : '{"success":false}';
    }


    public function entries() {

        $form = $this->param("form", null);
        $entries    = [];

        if ($form) {

            $frm     = "form".$form["_id"];
            $options = [];

            if ($filter = $this->param("filter", null)) $options["filter"] = $filter;
            if ($limit  = $this->param("limit", null))  $options["limit"] = $limit;
            if ($sort   = $this->param("sort", null))   $options["sort"] = $sort;
            if ($skip   = $this->param("skip", null))   $options["skip"] = $skip;

            $entries = $this->app->db->find("forms/{$frm}", $options);
        }

        return json_encode($entries->toArray());
    }

    public function removeentry(){

        $form = $this->param("form", null);
        $entryId    = $this->param("entryId", null);

        if ($form && $entryId) {

            $frm = "form".$form["_id"];

            $this->app->db->remove("forms/{$frm}", ["_id" => $entryId]);
        }

        return ($form && $entryId) ? '{"success":true}' : '{"success":false}';
    }

    public function emptytable(){

        $form = $this->param("form", null);

        if ($form) {

            $form = "form".$form["_id"];

            $this->app->db->remove("forms/{$form}", []);
        }

        return $form ? '{"success":true}' : '{"success":false}';
    }

    public function saveentry(){

        $form = $this->param("form", null);
        $entry      = $this->param("entry", null);

        if ($form && $entry) {

            $frm = "form".$form["_id"];

            $entry["modified"] = time();
            $entry["_uid"]     = @$this->user["_id"];

            if (!isset($entry["_id"])){
                $entry["created"] = $entry["modified"];
            }

            $this->app->db->save("forms/{$frm}", $entry);
        }

        return $entry ? json_encode($entry) : '{}';
    }

    public function export($formId) {

        if (!$this->app->module("auth")->hasaccess("Forms", 'manage.forms')) {
            return false;
        }

        $form = $this->app->db->findOneById("common/forms", $formId);

        if (!$form) return false;

        $col     = "form".$form["_id"];
        $entries = $this->app->db->find("forms/{$col}");

        return json_encode($entries, JSON_PRETTY_PRINT);
    }

}