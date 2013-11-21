<?php

namespace Cockpit\Controller;

class Settings extends \Cockpit\Controller {

    public function account() {

        if($data = $this->param("account", false)) {

            if(isset($data["password"])) {
                if(strlen($data["password"])){
                    $data["password"] = $this->app->hash($data["password"]);
                } else {
                    unset($data["password"]);
                }
            }

            $data["_id"] = $this->user["_id"];

            $this->data->cockpit->accounts->save($data);

            $this->app->helper("session")->write('app.auth', $data);

            return '{"success":true}';
        }

        $account = $this->data->cockpit->accounts->findOne([
            "_id" => $this->user["_id"]
        ]);

        unset($account["password"]);

        return $this->render('cockpit:views/settings/account.php', compact('account'));
    }

}