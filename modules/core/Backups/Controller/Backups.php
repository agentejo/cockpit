<?php

namespace Backups\Controller;

class Backups extends \Cockpit\Controller {

    public function index() {

        $backups = [];

        foreach ($this->app->helper("fs")->ls('*.zip', '#backups:') as $file) {

            if (!$file->isFile()) continue;
            if ($file->getExtension()!='zip') continue;

            $basename  = $file->getBasename('.zip');
            $parts     = explode('.', $basename);
            $timestamp = $parts[0];

            array_splice($parts, 0, 1);

            $backups[] = ["timestamp" => $timestamp, "info" => implode('.', $parts), "file" => $basename,  "size" => $this->app->helper("utils")->formatSize($file->getSize())];
        }

        if (count($backups)) {
            $backups = array_reverse($backups);
        }

        return $this->render('backups:views/index.php', compact('backups'));
    }

    public function create() {

        set_time_limit(0);

        $timestamp  = time();
        $target     = $this->param('target', 'site');
        $filename   = "{$timestamp}.{$target}.zip";
        $rootfolder = $this->app->path($target == 'cockpit' ? "#root:" : "site:");

        $this->app->helper("backup")->backup($rootfolder, $this->app->path("#backups:")."/{$filename}", function($file) {
            return preg_match('/cache/', $file) && !preg_match('/index\.html/', $file) || preg_match('/backups/', $file) && !preg_match('/index\.html/', $file);
        });

        return json_encode(["timestamp" => $timestamp, "info" => $target, "file" => "{$timestamp}.{$target}", "size" => $this->app->helper("utils")->formatSize(filesize($this->app->path("#backups:{$filename}")))]);
    }

    public function remove() {

        if ($file = $this->param("file", false)) {

            if ($file = $this->app->path("#backups:{$file}.zip")) {

                @unlink($file);

                return '{"success":true}';
            }
        }

        return false;
    }

    public function restore() {

    }
}