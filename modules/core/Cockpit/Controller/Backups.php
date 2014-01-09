<?php

namespace Cockpit\Controller;

class Backups extends \Cockpit\Controller {

    public function index() {

        $backups = [];

        foreach ($this->app->helper("filesystem")->ls('*.zip', 'backups:') as $file) {

            if(!$file->isFile()) continue;
            if($file->getExtension()!='zip') continue;

            $backups[] = ["timestamp" => $file->getBasename('.zip'), "size" => $this->app->helper("utils")->formatSize($file->getSize())];
        }

        return $this->render('cockpit:views/backups/index.php', compact('backups'));
    }

    public function create() {

        $zip        = new \ZipArchive();
        $timestamp  = time();
        $filename   = $timestamp.'.zip';
        $rootfolder = $this->app->path("site:");

        if (!$zip->open($this->app->path("backups:")."/{$filename}", \ZIPARCHIVE::CREATE)) {
            return false;
        }

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($rootfolder), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file) {

            $file = str_replace('\\', '/', $file);

            // Ignore "." and ".." folders
            if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) ) continue;
            if(preg_match('/\.git/', $file)) continue;
            if(preg_match('/\.DS_Store/', $file)) continue;
            if(preg_match('/cache/', $file) && !preg_match('/index\.html/', $file)) continue;
            if(preg_match('/backups/', $file) && !preg_match('/index\.html/', $file)) continue;

            $file = realpath($file);

            if (is_dir($file) === true) {
                $zip->addEmptyDir(str_replace($rootfolder, '', $file . '/'));
            }else if (is_file($file) === true){
                $zip->addFromString(str_replace($rootfolder, '', $file), file_get_contents($file));
            }
        }

        $zip->close();

        return json_encode(["timestamp" => $timestamp, "size" => $this->app->helper("utils")->formatSize(filesize($this->app->path("backups:/{$filename}")))]);
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