<?php

namespace Cockpit\Controller;

class Accounts extends \Cockpit\Controller {

    public function index() {

        $current  = $this->user["_id"];
        $accounts = $this->data->cockpit->accounts->find()->sort(["user" => 1])->toArray();


        foreach ($accounts as &$account) {
            $account["md5email"] = md5(@$account["email"]);
        }

        return $this->render('cockpit:views/accounts/index.php', compact('accounts', 'current'));
    }


    public function account($uid=null) {

        if(!$uid) {
            $uid = $this->user["_id"];
        }


        $account = $this->data->cockpit->accounts->findOne([
            "_id" => $uid
        ]);

        if(!$account) {
            return false;
        }

        unset($account["password"]);

        return $this->render('cockpit:views/accounts/account.php', compact('account', 'uid'));
    }

    public function create() {

        $uid     = null;
        $account = ["user"=>"", "email"=>"", "active"=>1];

        return $this->render('cockpit:views/accounts/account.php', compact('account', 'uid'));
    }

    public function save() {
        
        if($data = $this->param("account", false)) {


            if(isset($data["password"])) {
                if(strlen($data["password"])){
                    $data["password"] = $this->app->hash($data["password"]);
                } else {
                    unset($data["password"]);
                }
            }

            $this->data->cockpit->accounts->save($data);

            if($data["_id"] == $this->user["_id"]) {
                $this->app->helper("session")->write('app.auth', $data);
            }

            return json_encode($data);
        }

        return false;

    }

    public function remove() {

        if($data = $this->param("account", false)) {

            // user can't delete himself
            if($data["_id"] != $this->user["_id"]) {
                
                $this->data->cockpit->accounts->remove(["_id" => $data["_id"]]);

                return '{"success":true}';
            }
        }

        return false;
    }

}