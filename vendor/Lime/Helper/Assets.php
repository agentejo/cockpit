<?php

namespace Lime\Helper;

class Assets extends \Lime\Helper {

    /**
    * [style description]
    * @param  String $name
    * @return String
    */
    public function style($assets, $name, $path="", $cache=0, $version=false) {

        $path = $this->path($path);

        if(!$path) return null;

        $href = rtrim($this->pathToUrl($path), '/')."/{$name}.css".($version ? "?ver={$version}":"");
        $path.= "/{$name}.css";
        $tag  = '<link href="'.$href.'" type="text/css" rel="stylesheet" />'."\n";

        if($cache && file_exists($path) && (time() - filemtime($path)) < $cache) {
            return $tag;
        }

        file_put_contents($path, $this->compile($assets, "css"));

        return $tag;
    }

    /**
    * [script description]
    * @param  String $name
    * @return String
    */
    public function script($assets, $name, $path="", $cache=0, $version=false){

        $path = $this->path($path);

        if(!$path) return null;

        $src  = rtrim($this->pathToUrl($path), '/')."/{$name}.js".($version ? "?ver={$version}":"");
        $path.= "/{$name}.js";
        $tag  = '<script src="'.$src.'" type="text/javascript"></script>'."\n";

        if($cache && file_exists($path) && (time() - filemtime($path)) < $cache ) {
            return $tag;
        }

        file_put_contents($path, $this->compile($assets, "js"));

        return $tag;
    }

    public function style_and_script($assets, $name, $path="", $cache=0, $version=false) {
        echo $this->script($assets, $name, $path, $cache, $version);
        echo $this->style($assets, $name, $path, $cache, $version);
    }


    /**
    * [assets description]
    * @param  Array $assets
    * @return String         js or css
    */
    public function compile($assets, $type) {

        $self = $this;

        $rewriteCssUrls = function($content, $asset) use($self) {

            $source_dir = dirname($asset["file"]);
            $root_dir   = $self->app['docs_root'];

            $csspath  = "";

            if (strlen($root_dir) < strlen($source_dir)) {
                $csspath = '/'.trim(str_replace($root_dir, '', $source_dir), "/")."/";
            } else {
                // todo
            }

            $offset = 0;

            while(($pos = strpos($content, 'url(', $offset)) !== false){

                if(($urlend = strpos($content, ')', $pos))!==false) {

                    $path = trim(str_replace(array('"', "'"), "", substr($content, $pos+4, $urlend-($pos+4))));

                    if(!preg_match("#^(http|/|data\:)#",trim($path))){
                        $content = str_replace($path, $csspath.$path, $content);
                    }
                }

                $offset = $pos + 1;
            }


            return $content;
        };

        $output = array();

        foreach ((array)$assets as $file) {

            $asset = array(
                "ext"  => pathinfo($file, PATHINFO_EXTENSION),
                "file" => $file
            );

            $ext     = ($asset['ext']=="scss" || $asset['ext']=="less") ? "css":$asset['ext'];
            $content = '';

            if (strpos($file, ':') !== false && $____file = $this->app->path($file)) {
                $asset['file'] = $file = $____file;
            }

            if($ext!=$type) continue;

            switch ($asset['ext']) {

                case 'js':

                    $content = @file_get_contents($file);
                    break;

                case 'scss':
                case 'less':
                case 'css':

                    switch($asset['ext']) {
                        case 'scss':
                            $content = \Sass::parse($file);
                            break;
                        case 'less':
                            $content = \Less::parse($file);
                            break;
                        default:
                            $content = @file_get_contents($file);
                    }

                    $content = $rewriteCssUrls($content, $asset);

                    break;

                default:
                    continue;
            }

            $output[] = $content;
        }

        return implode("", $output);
    }

}