<?php

namespace Mediamanager\Controller;

class Mediamanager extends \Cockpit\Controller {

	protected $root;

	public function index(){

        if(!$this->app->module("auth")->hasaccess("Mediamanager","manage")) return false;

        return $this->render("mediamanager:views/index.php");
	}

    public function thumbnail($image, $width = 50, $height = 50) {

        $image  = base64_decode($image);
        $imgurl = $this->app->module("mediamanager")->thumbnail($image, $width, $height);
        $fail   = (strpos($imgurl, 'data:')===0);
        $type   = 'gif';
        $data   = base64_decode('R0lGODlhAQABAJEAAAAAAP///////wAAACH5BAEHAAIALAAAAAABAAEAAAICVAEAOw=='); // empty 1x1 gif

        if(!$fail) {

            $info = pathinfo($imgurl);
            $type = $info['extension'];
            $data = file_get_contents($this->app['docs_root'].$imgurl);
        }

        header("Content-type: image/{$type}");
        $this->app->stop($data);
    }


    public function api() {

        $cmd       = $this->param("cmd", false);
        $mediapath = trim($this->module("auth")->getGroupSetting("media.path", '/'), '/');

        $this->root = rtrim($this->app->path("site:{$mediapath}"), '/');

        if(file_exists($this->root) && in_array($cmd, get_class_methods($this))){
            return $this->{$cmd}();
        }

        return false;
    }

    protected function ls() {

        $data     = array("folders"=>array(), "files"=>array());
        $toignore = [
            '.svn', '_svn', 'cvs', '_darcs', '.arch-params', '.monotone', '.bzr', '.git', '.hg',
            '.ds_store', '.thumb'
        ];

		if($path = $this->param("path", false)){

            $dir = $this->root.'/'.trim($path, '/');
            $data["path"] = $dir;

            if(file_exists($dir)){

               foreach (new \DirectoryIterator($dir) as $file) {

               		if($file->isDot()) continue;

                    $filename = $file->getFilename();

                    if($filename[0]=='.' && in_array(strtolower($filename), $toignore)) continue;

                    $data[$file->isDir() ? "folders":"files"][] = array(
                        "is_file" => !$file->isDir(),
                        "is_dir" => $file->isDir(),
                        "is_writable" => is_writable($file->getPathname()),
                        "name" => $filename,
                        "path" => trim($path.'/'.$file->getFilename(), '/'),
                        "url"  => $this->app->pathToUrl($file->getPathname()),
                        "size" => $file->isDir() ? "" : $this->app->helper("utils")->formatSize($file->getSize()),
                        "lastmodified" => $file->isDir() ? "" : date("d.m.y H:m", $file->getMTime()),
                    );
                }
            }
        }

    	return json_encode($data);
    }

    protected function upload() {

        $files      = isset($_FILES['files']) ? $_FILES['files'] : [];
        $path       = $this->param('path', false);
        $targetpath = $this->root.'/'.trim($path, '/');
        $uploaded   = [];


        if (isset($files['name']) && $path && file_exists($targetpath)) {
            for ($i = 0; $i < count($files['name']); $i++) {
                if (!$files['error'][$i] && move_uploaded_file($files['tmp_name'][$i], $targetpath.'/'.$files['name'][$i])) {
                    $uploaded[] = $files['name'][$i];
                }
            }
        }

        return json_encode($uploaded);
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

        $paths = (array)$this->param('paths', array());

        foreach ($paths as $path) {

            $delpath = $this->root.'/'.trim($path, '/');

            if(is_dir($delpath)) {
                $this->_rrmdir($delpath);
            }

            if(is_file($delpath)){
                unlink($delpath);
            }
        }

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
        }

        return json_encode(array("success"=>true));
    }

    protected function readfile() {

        $path = $this->param('path', false);
        $file = $this->root.'/'.trim($path, '/');

        if($path && file_exists($file)) {
            echo file_get_contents($file);
        }

        $this->app->stop();
    }

    protected function writefile() {

        $path    = $this->param('path', false);
        $content = $this->param('content', false);
        $file    = $this->root.'/'.trim($path, '/');
        $ret     = false;

        if($path && file_exists($file) && $content!==false) {
            $ret = file_put_contents($file, $content);
        }

        return json_encode(array("success"=>$ret));
    }

    protected function download() {

        $path = $this->param('path', false);
        $file = $this->root.'/'.trim($path, '/');

        if(!$path && !file_exists($file)) {
            $this->app->stop();
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

    protected function getfilelist() {

        $list = [];
        $toignore = [
            '\.svn', '_svn', 'cvs', '_darcs', '\.arch-params', '\.monotone', '\.bzr', '\.git', '\.hg', '\.ds_store', '\.thumb', '\/cache'
        ];

        $toignore = '/('.implode('|',$toignore).')/i';

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->root)) as $file) {

            if($file->isDir()) continue;

            $filename = $file->getFilename();

            if($filename[0]=='.' || preg_match($toignore, $file->getPathname())) continue;

            $path = trim(str_replace(['\\', $this->root], ['/',''], $file->getPathname()), '/');

            $list[] = [
                "is_file" => true,
                "is_dir" => false,
                "is_writable" => is_writable($file->getPathname()),
                "name" => $filename,
                "path" => $path,
                "dir" => dirname($path),
                "is_writable" => is_writable($file->getPathname()),
                "url"  => $this->app->pathToUrl($file->getPathname()),
            ];
        }

        return json_encode($list);
    }

    public function savebookmarks() {

        if($bookmarks = $this->param('bookmarks', false)) {
            $this->memory->set("mediamanager.bookmarks.".$this->user["_id"], $bookmarks);
        }

        return json_encode($bookmarks);
    }

    public function loadbookmarks() {

        return json_encode($this->app->memory->get("mediamanager.bookmarks.".$this->user["_id"], ["folders"=>[], "files"=>[]]));
    }

}