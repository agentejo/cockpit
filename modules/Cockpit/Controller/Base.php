<?php

namespace Cockpit\Controller;

class Base extends \Cockpit\Controller {

    public function dashboard() {

        $stream = array();

        return $this->render('cockpit:views/base/dashboard.php', compact('stream'));
    }

    public function settings() {

        if($data = $this->param("profile", false)) {

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

        $profile = $this->data->cockpit->accounts->findOne([
            "_id" => $this->user["_id"]
        ]);

        unset($profile["password"]);

        return $this->render('cockpit:views/base/settings.php', compact('profile'));
    }

}