<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cockpit\Controller;

class Settings extends \Cockpit\AuthController {


    public function index() {
        return $this->render('cockpit:views/settings/index.php');
    }

    public function info() {

        if (!$this->module('cockpit')->hasaccess('cockpit', 'info')) {
            return $this->helper('admin')->denyRequest();
        }

        $info                  = [];

        $info['app']           = $this->app->helper('admin')->data['cockpit'];

        $info['system']        = php_uname();
        $info['phpversion']    = phpversion();
        $info['sapi_name']     = php_sapi_name();
        $info['extensions']    = get_loaded_extensions();
        $info['mailer']        = $this->app->retrieve('config/mailer', false);

        $info['jobs_queue'] = [
            'running' => $this->app->helper('jobs')->isRunnerActive(),
            'cntjobs' => $this->app->helper('jobs')->countJobs()
        ];

        $update = $this->getUptdateInfo();

        return $this->render('cockpit:views/settings/info.php', compact('info', 'update'));
    }

    public function edit($createconfig = false) {

        if (!$this->module('cockpit')->isSuperAdmin()) {
            return false;
        }

        if ($createconfig && !$this->app->path(COCKPIT_CONFIG_PATH)) {

            if ($this->app->helper('fs')->mkdir(dirname(COCKPIT_CONFIG_PATH))) {
                $this->app->helper('fs')->write(COCKPIT_CONFIG_PATH, "# Cockpit settings\n");
            }
        }

        $configexists = $this->app->path(COCKPIT_CONFIG_PATH);

        return $this->render('cockpit:views/settings/edit.php', compact('configexists'));
    }

    public function update() {

        if (!$this->module('cockpit')->isSuperAdmin()) {
            return false;
        }

        $update = $this->getUptdateInfo();

        $this->app->trigger('cockpit.update.before', [$update]);
        $ret = $this->app->helper('updater')->update($update['zipfile'], $update['target'], $update['options']);
        $this->app->trigger('cockpit.update.after', [$update]);

        return $ret;
    }

    protected function getUptdateInfo() {

        $update = new \ArrayObject(array_merge([
            'package.json' => 'https://raw.githubusercontent.com/agentejo/cockpit/master/package.json',
            'zipfile' => 'https://github.com/agentejo/cockpit/archive/master.zip',
            'target'  => COCKPIT_DIR,
            'options' => ['zipRoot' => 'cockpit-master']
        ], $this->app->retrieve('config/update', [])));

        return $update;
    }
}
