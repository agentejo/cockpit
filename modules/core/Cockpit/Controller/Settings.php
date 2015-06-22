<?php

namespace Cockpit\Controller;

class Settings extends \Cockpit\AuthController {


    public function index() {

        return $this->render('cockpit:views/settings/index.php');
    }

    public function info() {

        $info                  = [];

        $info["app"]           = $this->app->helper('admin')->data['cockpit'];

        $info['system']        = php_uname();
        $info['phpversion']    = phpversion();
        $info['sapi_name']     = php_sapi_name();
        $info['extensions']    = get_loaded_extensions();

        $info["mailer"]        = $this->app->retrieve("config/mailer", false);

        return $this->render('cockpit:views/settings/info.php', compact('info'));
    }

    public function edit($createconfig = false) {

        if ($this->app['user']['group'] !== 'admin') {
            return false;
        }

        if ($createconfig && !$this->app->path('#root:config/config.yaml')) {
            if ($this->app->helper('fs')->mkdir('#root:config')) {
                $this->app->helper('fs')->write('#root:config/config.yaml', "# Cockpit settings\n");
            }
        }

        $configexists = $this->app->path('#root:config/config.yaml');

        return $this->render('cockpit:views/settings/edit.php', compact('configexists'));
    }
}
