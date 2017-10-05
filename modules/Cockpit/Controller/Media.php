<?php

namespace Cockpit\Controller;

class Media extends \Cockpit\AuthController {

    protected $root;

    public function api() {

        $cmd       = $this->param("cmd", false);
        $mediapath = $this->module('cockpit')->getGroupVar('finder.path', '');

        $this->root = rtrim($this->app->path("site:{$mediapath}"), '/');

        if (file_exists($this->root) && in_array($cmd, get_class_methods($this))){

            $this->app->response->mime = 'json';

            return $this->{$cmd}();
        }

        return false;
    }

    protected function ls() {

        $data     = array("folders"=>array(), "files"=>array());
        $toignore = [
            '.svn', '_svn', 'cvs', '_darcs', '.arch-params', '.monotone', '.bzr', '.git', '.hg',
            '.ds_store', '.thumb', '.idea'
        ];

        $cpfolder = $this->app->path('#root:');
        $sitefolder = $this->app->path('site:');
        $isSuperAdmin = $this->module('cockpit')->isSuperAdmin();

        if ($path = $this->param("path", false)){

            $dir = $this->root.'/'.trim($path, '/');
            $data["path"] = $dir;

            if (file_exists($dir)){

               foreach (new \DirectoryIterator($dir) as $file) {

                    if ($file->isDot()) continue;
                    if ($file->isDir() && $file->getRealPath() == $cpfolder && !$isSuperAdmin ) continue;

                    $filename = $file->getFilename();

                    if ($filename[0]=='.' && in_array(strtolower($filename), $toignore)) continue;

                    $isDir = $file->isDir();

                    $data[$file->isDir() ? "folders":"files"][] = array(
                        "is_file" => !$isDir,
                        "is_dir" => $isDir,
                        "is_writable" => is_writable($file->getPathname()),
                        "name" => $filename,
                        "path" => trim($path.'/'.$file->getFilename(), '/'),
                        "rel_site_path" => trim(str_replace($sitefolder, '', $file->getPathname()), '/'),
                        "url"  => $this->app->pathToUrl($file->getPathname()),
                        "size" => $isDir ? "" : $this->app->helper("utils")->formatSize($file->getSize()),
                        "filesize" => $isDir ? "" : $file->getSize(),
                        "ext"  => $isDir ? "" : strtolower($file->getExtension()),
                        "lastmodified" => $file->isDir() ? "" : date("d.m.y H:i", $file->getMTime()),
                        "modified" => $file->isDir() ? "" : $file->getMTime(),
                    );
                }
            }
        }

        return $data;
    }

    protected function upload() {

        $files      = isset($_FILES['files']) ? $_FILES['files'] : [];
        $path       = $this->param('path', false);
        $targetpath = $this->root.'/'.trim($path, '/');
        $uploaded   = [];
        $failed     = [];

        // absolute paths for hook
        $_uploaded  = [];
        $_failed    = [];

        if (isset($files['name']) && $path && file_exists($targetpath)) {
            for ($i = 0; $i < count($files['name']); $i++) {

                // clean filename
                $clean = preg_replace('/[^a-zA-Z0-9-_\.]/','', str_replace(' ', '-', $files['name'][$i]));

                if (!$files['error'][$i] && move_uploaded_file($files['tmp_name'][$i], $targetpath.'/'.$clean)) {
                    $uploaded[]  = $files['name'][$i];
                    $_uploaded[] = $targetpath.'/'.$clean;
                } else {
                    $failed[]    = ['file' => $files['name'][$i], 'error' => $files['error'][$i]];
                    $_failed[]   = $targetpath.'/'.$clean;
                }
            }
        }

        $this->app->trigger('cockpit.media.upload', [$_uploaded, $_failed]);

        return json_encode(['uploaded' => $uploaded, 'failed' => $failed]);
    }

    protected function createfolder() {

        $path = $this->param('path', false);
        $name = $this->param('name', false);
        $ret  = false;

        if ($name && $path) {
            $ret = mkdir($this->root.'/'.trim($path, '/').'/'.$name);
        }

        return json_encode(array("success"=>$ret));
    }

    protected function createfile() {

        $path = $this->param('path', false);
        $name = $this->param('name', false);
        $ret  = false;

        if ($name && $path) {
            $ret = @file_put_contents($this->root.'/'.trim($path, '/').'/'.$name, "");
        }

        return json_encode(array("success"=>$ret));
    }


    protected function removefiles() {

        $paths     = (array)$this->param('paths', array());
        $deletions = [];

        foreach ($paths as $path) {

            $delpath = $this->root.'/'.trim($path, '/');

            if (is_dir($delpath)) {
                $this->_rrmdir($delpath);
            }

            if (is_file($delpath)){
                unlink($delpath);
            }

            $deletions[] = $delpath;
        }

        $this->app->trigger('cockpit.media.removefiles', [$deletions]);

        return json_encode(array("success"=>true));
    }

    protected function _rrmdir($dir) {

        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") $this->_rrmdir($dir."/".$object); else unlink($dir."/".$object);
                }
            }

            reset($objects);
            rmdir($dir);
        }
    }

    protected function rename() {

        $path = $this->param('path', false);
        $name = $this->param('name', false);

        if ($path && $name) {
            $source = $this->root.'/'.trim($path, '/');
            $target = dirname($source).'/'.$name;

            rename($source, $target);
            $this->app->trigger('cockpit.media.rename', [$source, $target]);
        }

        return json_encode(array("success"=>true));
    }

    protected function readfile() {

        $path = $this->param('path', false);
        $file = $this->root.'/'.trim($path, '/');

        if ($path && file_exists($file)) {
            echo file_get_contents($file);
        }

        $this->app->stop();
    }

    protected function writefile() {

        $path    = $this->param('path', false);
        $content = $this->param('content', false);
        $file    = $this->root.'/'.trim($path, '/');
        $ret     = false;

        if ($path && file_exists($file) && $content!==false) {
            $ret = file_put_contents($file, $content);
        }

        return json_encode(array("success"=>$ret));
    }

    protected function unzip() {

        $return  = ['success' => false];

        $path    = $this->param('path', false);
        $zip     = $this->param('zip', false);

        if ($path && $zip) {

            $path =  $this->root.'/'.trim($path, '/');
            $zip  =  $this->root.'/'.trim($zip, '/');

            $za = new \ZipArchive;

            if ($za->open($zip)) {

                if ($za->extractTo($path)) {
                    $return = ['success' => true];
                }

                $za->close();
            }
        }

        return json_encode($return);
    }

    protected function download() {

        $path = $this->param('path', false);
        $file = $this->root.'/'.trim($path, '/');

        if (!$path && !file_exists($file)) {
            $this->app->stop();
        }

        if (is_dir($file)) {
            return $this->downloadfolder();
        }

        $pathinfo = $path_parts = pathinfo($file);

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=\"".$pathinfo["basename"]."\";" );
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".filesize($file));
        readfile($file);

        $this->app->stop();
    }

    protected function downloadfolder() {

        $path   = $this->param('path', false);
        $folder = $this->root.'/'.trim($path, '/');

        if (!$path && !file_exists($folder)) {
            $this->app->stop();
        }

        $files   = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder), \RecursiveIteratorIterator::LEAVES_ONLY);
        $zipfile = $this->app->path('#tmp:').'/'.basename($folder).'_'.md5($folder).'.zip';
        $zip     = new \ZipArchive();

        $zip->open($zipfile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($files as $name => $file) {

            if ($file->isDir()) continue;

            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($folder) + 1);
            $zip->addFile($filePath, $relativePath);
        }

        $zip->close();

        header('Location: '.$this->app->pathToUrl($zipfile));

        $this->app-stop();
    }

    protected function getfilelist() {

        $list = [];
        $toignore = [
            '\.svn', '_svn', 'cvs', '_darcs', '\.arch-params', '\.monotone', '\.bzr', '\.git', '\.hg', '\.ds_store', '\.thumb', '\/cache'
        ];

        $toignore = '/('.implode('|',$toignore).')/i';

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->root)) as $file) {

            if ($file->isDir()) continue;

            $filename = $file->getFilename();

            if ($filename[0]=='.' || preg_match($toignore, $file->getPathname())) continue;

            $path = trim(str_replace(['\\', $this->root], ['/',''], $file->getPathname()), '/');

            $list[] = [
                "is_file" => true,
                "is_dir" => false,
                "is_writable" => is_writable($file->getPathname()),
                "name" => $filename,
                "path" => $path,
                "dir" => dirname($path),
                "url"  => $this->app->pathToUrl($file->getPathname()),
            ];
        }

        return json_encode($list);
    }

    public function savebookmarks() {

        if ($bookmarks = $this->param('bookmarks', false)) {
            $this->memory->set("mediamanager.bookmarks.".$this->user["_id"], $bookmarks);
        }

        return json_encode($bookmarks);
    }

    public function loadbookmarks() {

        return json_encode($this->app->memory->get("mediamanager.bookmarks.".$this->user["_id"], ["folders"=>[], "files"=>[]]));
    }

}
