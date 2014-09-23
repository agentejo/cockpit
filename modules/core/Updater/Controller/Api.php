<?php

namespace Updater\Controller;

class Api extends \Cockpit\Controller {


    public function check(){

        set_time_limit(0);

        $return = ["error"=>false];

        $info = json_decode($this->app->helper("fs")->read("#root:package.json"), true);
        $data = @file_get_contents($info['check_url']);

        if (strlen($data) && $json = json_decode($data, true)) {
            $return["local"]  = $info;
            $return["stable"] = $json;
        } else {
            $return["error"] = $this->app->helper("i18n")->get("Failed loading package information");
        }

        return json_encode($return);
    }


    public function update($step = 0) {

        set_time_limit(0);

        $info    = json_decode($this->app->helper("fs")->read("#root:package.json"), true);
        $message = '';
        $success = false;

        if ($info && $version = $this->param("version", false)) {

            switch ($step) {
                // download release
                case 1:

                    $zipurl  = str_replace('%version%', $version, $info['release_zip_url']);

                    if (!is_writable($this->app->path('#root:'))) {
                        $message = 'Cockpit folder is not writable!';
                    } else {

                        if (file_put_contents($this->app->path("tmp:")."/{$version}.zip", $handle = @fopen($zipurl, 'r'))) {

                            $success = true;

                            if ($this->param("backup", true)) {

                                // backup current Cockpit version
                                $filename = time().'.cockpit-'.$info['version'].'.zip';

                                $this->app->helper("backup")->backup($this->app->path('#root:'), $this->app->path("#backups:")."/{$filename}", function($file) {
                                    return preg_match('/cache/', $file) && !preg_match('/index\.html/', $file) || preg_match('/backups/', $file) && !preg_match('/index\.html/', $file);
                                });
                            }

                        } else {
                            $message = "Couldn't download {$version} release!";
                        }

                        @fclose($handle);
                    }

                    return json_encode(["success" => $success, "message" => $message]);

                    break;

                // extract
                case 2:

                    if ($this->app->path("tmp:{$version}.zip") && $this->app->helper("fs")->mkdir("tmp:{$version}", 0777)) {

                        $zip     = new \ZipArchive;
                        $zipfile = $this->app->path("tmp:{$version}.zip");

                        if ($zip->open($zipfile) === true) {

                            $folder = $this->app->path("tmp:{$version}");

                            $success = $zip->extractTo($folder) ? $zip->close() : false;

                        }
                    }

                    if (!$success) {
                        $message = 'Extracting release file failed!';
                    }

                    return json_encode(["success" => $success, "message" => $message]);

                    break;

                // override
                case 3:

                    if ($folder = $this->app->path("tmp:{$version}")) {

                        $fs       = $this->app->helper("fs");
                        $root     = $this->app->path('#root:');
                        $distroot = false;

                        // find cockpit dist root
                        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder)) as $file) {
                            if ($file->getFilename() == 'package.json') {
                                $distroot = dirname($file->getRealPath());
                                break;
                            }
                        }

                        if ($distroot) {

                            // clean existing installation

                            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root), \RecursiveIteratorIterator::SELF_FIRST);

                            foreach ($files as $file) {

                                if (!$file->isFile()) continue;
                                if (preg_match('/(custom\/|storage\/|modules\/addons)/', $file)) continue;

                                @unlink($file->getRealPath());
                            }

                            $fs->removeEmptySubFolders($root);
                            $fs->copy($distroot, $root);
                        }

                        $fs->delete($folder);

                        $success = $distroot ? true : false;
                    }

                    if (!$success) {
                        $message = 'Override current release failed!';
                    }

                    return json_encode(["success" => $success, "message" => $message]);

                    break;

                // cleanup
                case 4:

                    $this->module('cockpit')->clearCache();

                    return '{"success": true}';

                    break;
            }

        }

        return false;
    }
}
