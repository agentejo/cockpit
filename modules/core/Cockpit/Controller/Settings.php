<?php

namespace Cockpit\Controller;

class Settings extends \Cockpit\Controller {


    public function general() {

        $registry = json_encode((object)$this->app->memory->get("cockpit.api.registry", []));
        $tokens   = $this->app->db->getKey("cockpit/settings", "cockpit.api.tokens", null);
        $locales  = json_encode($this->app->db->getKey("cockpit/settings", "cockpit.locales", []));

        if (!$tokens) {
            $tokens = new \stdClass;
        }

        return $this->render('cockpit:views/settings/general.php', compact('tokens', 'registry', 'locales'));
    }

    public function info() {

        $info                  = [];

        $info["app"]           = json_decode($this->app->helper("fs")->read("#root:package.json"), true);

        $info['system']        = php_uname();
        $info['phpversion']    = phpversion();
        $info['sapi_name']     = php_sapi_name();
        $info['extensions']    = get_loaded_extensions();

        $info["mailer"]        = $this->app->retrieve("app.config/mailer", false);
        $info["sizeCache"]     = $this->app->helper("utils")->formatSize($this->app->helper("fs")->getDirSize("cache:"));
        $info["sizeData"]      = $this->app->helper("utils")->formatSize($this->app->helper("fs")->getDirSize("data:"));
        $info['folders']       = [];

        foreach (['cache:', 'cache:assets', 'cache:thumbs', 'data:'] as $dir) {
            $info['folders'][$dir] = is_writable($this->app->path($dir));
        }

        return $this->render('cockpit:views/settings/info.php', compact('info'));
    }


    public function test($case) {

        switch ($case) {
            case 'email':

                $email = $this->param("email", false);

                if ($email) {
                    $ret = $this->app->mailer->mail($email, "Test Email", "It seems your Server can send Emails with the current mailer settings.");
                } else {
                    $ret = false;
                }

                return json_encode(["status"=>$ret]);

                break;
        }

        return false;
    }

    public function clearcache() {

        return json_encode($this->module('cockpit')->clearCache());
    }

    public function vacuumdata() {

        $this->helper("history")->clear();

        foreach ($this->app->helper("fs")->ls('*.sqlite', 'data:') as $file) {
            $db = new \PDO("sqlite:".$file->getRealPath());
            @$db->query("VACUUM");
            @$db->exec("VACUUM");
        }

        return json_encode(["size"=>$this->app->helper("utils")->formatSize($this->app->helper("fs")->getDirSize("data:"))]);
    }

    public function saveTokens() {

        $tokens = $this->param("tokens", false);

        if ($tokens !== false) {

            if (count($tokens)) {
                $tokens = (array) $tokens;
            } else {
                $tokens = null;
            }

            $this->app->db->setKey("cockpit/settings", "cockpit.api.tokens", $tokens);

            return ["success"=>true];
        }

        return false;
    }

    public function saveRegistry() {

        $registry = $this->param("registry", false);

        if ($registry !== false) {

            $this->app->memory->set("cockpit.api.registry", (object)$registry);

            return ["success"=>true];
        }

        return false;
    }

    public function saveLocals() {

        $locals = $this->param("locals", []);

        $this->app->db->setKey("cockpit/settings", "cockpit.locales", $locals);

        return ["success"=>true];
    }
}
