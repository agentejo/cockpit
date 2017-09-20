<?php

namespace Cockpit\Controller;

class Accounts extends \Cockpit\AuthController {

    public function index() {

        if (!$this->module('cockpit')->hasaccess('cockpit', 'accounts')) {
            return $this->helper('admin')->denyRequest();
        }

        $current  = $this->user["_id"];
        $accounts = $this->storage->find("cockpit/accounts", [
            "filter" => $this->user["group"]=="admin" ? null:["_id"=>$current],
            "sort"   => ["user" => 1]
        ])->toArray();

        foreach ($accounts as &$account) {
            $account["md5email"] = md5(@$account["email"]);
        }

        return $this->render('cockpit:views/accounts/index.php', compact('accounts', 'current'));
    }


    public function account($uid=null) {

        if (!$uid) {
            $uid = $this->user["_id"];
        }

        $account = $this->app->storage->findOne("cockpit/accounts", ["_id" => $uid]);

        if (!$account) {
            return false;
        }

        unset($account["password"]);

        $languages = $this->getLanguages();
        $groups    = $this->module('cockpit')->getGroups();

        return $this->render('cockpit:views/accounts/account.php', compact('account', 'uid', 'languages', 'groups'));
    }

    public function create() {

        $uid       = null;
        $account   = ["user"=>"", "email"=>"", "active"=>true, "group"=>"admin", "i18n"=>$this->app->helper("i18n")->locale];

        $languages = $this->getLanguages();
        $groups    = $this->module('cockpit')->getGroups();

        return $this->render('cockpit:views/accounts/account.php', compact('account', 'uid', 'languages', 'groups'));
    }

    public function save() {

        if ($data = $this->param("account", false)) {

            $data["_modified"] = time();

            if (!isset($data['_id'])) {
                $data["_created"] = $data["_modified"];
            }

            if (isset($data["password"])) {

                if (strlen($data["password"])){
                    $data["password"] = $this->app->hash($data["password"]);
                } else {
                    unset($data["password"]);
                }
            }

            $this->app->storage->save("cockpit/accounts", $data);

            if (isset($data["password"])) {
                unset($data["password"]);
            }

            if ($data["_id"] == $this->user["_id"]) {
                $this->module("cockpit")->setUser($data);
            }

            return json_encode($data);
        }

        return false;

    }

    public function remove() {

        if ($data = $this->param("account", false)) {

            // user can't delete himself
            if ($data["_id"] != $this->user["_id"]) {

                $this->app->storage->remove("cockpit/accounts", ["_id" => $data["_id"]]);

                return '{"success":true}';
            }
        }

        return false;
    }

    public function find() {

        $options = [
            "sort"   => ["user" => 1]
        ];

        if ($filter = $this->param('filter')) {

            $options['filter'] = $filter;

            if (is_string($filter)) {

                $options['filter'] = [
                    '$or' => [
                        ['name' => ['$regex' => $filter]],
                        ['user' => ['$regex' => $filter]],
                        ['email' => ['$regex' => $filter]],
                    ]
                ];
            }
        }

        $accounts = $this->storage->find("cockpit/accounts", $options)->toArray();

        foreach ($accounts as &$account) {
            $account["md5email"] = md5(@$account["email"]);
        }

        return $accounts;
    }

    protected function getLanguages() {

        $languages = [['i18n' => 'en', 'language' => 'English']];

        foreach ($this->app->helper('fs')->ls('*.php', '#config:cockpit/i18n') as $file) {

            $lang     = include($file->getRealPath());
            $i18n     = $file->getBasename('.php');
            $language = isset($lang['@meta']['language']) ? $lang['@meta']['language'] : $i18n;

            $languages[] = ['i18n' => $i18n, 'language'=> $language];
        }

        return $languages;
    }

}
