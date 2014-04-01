<?php

namespace Updater\Controller;

class Api extends \Cockpit\Controller {


    public function check(){

        set_time_limit(0);

        $return = ["error"=>false];

        $info = json_decode($this->app->helper("fs")->read("#root:package.json"), true);
        $data = @file_get_contents($info['check_url']);

        if (strlen($data) && $json = json_decode($data, true)) {
            $return["local"]   = $info;
            $return["current"] = $json;
        } else {
            $return["error"] = $this->app->helper("i18n")->get("Failed loading package information");
        }

        return json_encode($return);
    }


    public function update($step = 0) {

        set_time_limit(0);

        if ($version = $this->param("version", false)) {

            switch ($step) {
                // upload
                case 1:

                    $success = false;

                    if(file_put_contents($this->app->path("tmp:")."/{$version}.zip", $handle = fopen("https://github.com/aheinze/cockpit/archive/{$version}.zip", 'r'))) {
                        $success = true;
                    }

                    @fclose($handle);

                    return json_encode(["success" => $success]);

                    break;

                // extract
                case 2:

                    if ($this->app->path("tmp:{$version}.zip") && $this->app->helper("fs")->mkdir("tmp:{$version}", 0777)) {

                        $zip     = new \ZipArchive;
                        $zipfile = $this->app->path("tmp:{$version}.zip");

                        if ($zip->open($zipfile) === true) {

                            $folder = $this->app->path("tmp:{$version}");

                            $success = $zip->extractTo($folder) ? $zip->close() : false;

                            return json_encode(["success" => $success]);

                        } else {
                            return '{"success": false}';
                        }

                    } else {
                        return '{"success": false}';
                    }

                    break;

                // override
                case 3:

                    $success = false;

                    if ($folder = $this->app->path("tmp:{$version}")) {

                        $distroot = false;

                        // find cockpit root
                        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder)) as $file) {
                            if($file->getFilename() == 'package.json') {
                                $distroot = dirname($file->getRealPath());
                                break;
                            }
                        }

                        if ($distroot) {
                            $this->app->helper("fs")->copy($distroot, $this->app->path('#root:'));
                        }

                        $success = $distroot ? true : false;

                    }

                    return json_encode(["success" => $success]);

                    break;

                // cleanup
                case 4:

                    $this->app->helper("fs")->delete("tmp:{$version}");

                    $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->app->path("cache:")), \RecursiveIteratorIterator::SELF_FIRST);

                    foreach ($files as $file) {

                        if(!$file->isFile()) continue;
                        if(preg_match('/(.gitkeep|index\.html)$/', $file)) continue;

                        @unlink($file->getRealPath());
                    }

                    return '{"success": true}';

                    break;
            }

        }

        return false;
    }

}