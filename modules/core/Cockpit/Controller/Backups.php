<?php

namespace Cockpit\Controller;

class Backups extends \Cockpit\Controller {

    public function index() {

        $backups = [];

        foreach ($this->app->helper("fs")->ls('*.zip', 'backups:') as $file) {

            if(!$file->isFile()) continue;
            if($file->getExtension()!='zip') continue;

            $backups[] = ["timestamp" => $file->getBasename('.zip'), "size" => $this->app->helper("utils")->formatSize($file->getSize())];
        }

        if (count($backups)) {
            $backups = array_reverse($backups);
        }

        return $this->render('cockpit:views/backups/index.php', compact('backups'));
    }

    public function create() {

        set_time_limit(0);

        $timestamp  = time();
        $filename   = $timestamp.'.zip';
        $rootfolder = $this->app->path("site:");

        $this->app->helper("backup")->backup($rootfolder, $this->app->path("backups:")."/{$filename}", function($file) {
            return preg_match('/cache/', $file) && !preg_match('/index\.html/', $file) || preg_match('/backups/', $file) && !preg_match('/index\.html/', $file);
        });

        return json_encode(["timestamp" => $timestamp, "size" => $this->app->helper("utils")->formatSize(filesize($this->app->path("backups:{$filename}")))]);
    }

    public function remove() {

        if($timestamp = $this->param("timestamp", false)) {

            if($file = $this->app->path("backups:{$timestamp}.zip")) {

                @unlink($file);

                return '{"success":true}';
            }
        }

        return false;
    }

    public function restore() {

    }
}