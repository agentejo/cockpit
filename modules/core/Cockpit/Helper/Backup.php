<?php

namespace Cockpit\Helper;

class Backup extends \Lime\Helper {

    public function backup($sourcefolder, $target, $ignorecallback = null) {

        $sourcefolder = rtrim(str_replace(DIRECTORY_SEPARATOR, '/', $sourcefolder), '/').'/';
        $target       = str_replace(DIRECTORY_SEPARATOR, '/', $target);
        $zip          = new \ZipArchive();

        if (!$zip->open($target, \ZIPARCHIVE::CREATE)) {
            return false;
        }

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($sourcefolder), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file) {

            $file = str_replace(DIRECTORY_SEPARATOR, '/', $file);

            // Ignore "." and ".." folders
            if(in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) ) continue;
            if(preg_match('/\.git/', $file)) continue;
            if(preg_match('/\.DS_Store/', $file)) continue;
            if($ignorecallback && $ignorecallback($file)===true) continue;

            $file = realpath($file);

            if (is_dir($file) === true) {
                $zip->addEmptyDir(str_replace($sourcefolder, '', $file . '/'));
            }else if (is_file($file) === true){
                $zip->addFromString(str_replace($sourcefolder, '', $file), file_get_contents($file));
            }
        }

        $zip->close();

        return true;
    }
}