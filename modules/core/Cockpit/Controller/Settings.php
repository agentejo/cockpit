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

    public function info() {

        $info                  = [];
        $info['system']        = php_uname();
        $info['phpversion']    = phpversion();
        $info['sapi_name']     = php_sapi_name();

        $info['folders']       = [];

        foreach (['cache:', 'cache:assets', 'cache:thumbs', 'data:'] as $dir) {
            $info['folders'][$dir] = is_writable($this->app->path($dir));
        }

        $info["mailer"]        = $this->app->retrieve("app.config/mailer", false);

        return $this->render('cockpit:views/settings/info.php', compact('info'));
    }


    public function test($case) {

        switch ($case) {
            case 'email':
                
                $email = $this->param("email", false);

                if($email) {
                    $ret = $this->app->mailer->mail($email, "Test Email", "It seems your Server can send Emails with the current mailer settings.");
                } else {
                    $ret = false;
                }

                return json_encode(["status"=>$ret]);

                break;
        }

        return false;
    }

}