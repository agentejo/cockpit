<?php

namespace Cockpit\Controller;

class Settings extends \Cockpit\Controller {


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

    public function clearcache() {

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->app->path("cache:")), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file) {
            
            if(!$file->isFile()) continue;
            if(preg_match('/(.gitkeep|index\.html)$/', $file)) continue;

            @unlink($file->getRealPath());
        }

        return json_encode(["size"=>$this->app->helper("utils")->formatSize($this->app->helper("fs")->getDirSize("cache:"))]);
    }
    
    public function vacuumdata() {

        foreach ($this->app->helper("fs")->ls('*.sqlite', 'data:') as $file) {
            $db = new \PDO("sqlite:".$file->getRealPath());
            @$db->query("VACUUM");
        }


        return json_encode(["size"=>$this->app->helper("utils")->formatSize($this->app->helper("fs")->getDirSize("data:"))]);
    }


}