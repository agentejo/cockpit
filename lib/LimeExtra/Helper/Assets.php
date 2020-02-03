<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LimeExtra\Helper;

/**
 * Assets class.
 */
class Assets extends \Lime\Helper {

    /**
     * Compile styles and return in a link tag
     *
     * @param  Array   $assets
     * @param  String  $name
     * @param  String  $path
     * @param  Float   $cache
     * @param  Boolean $version
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

        $result = $this->compile($assets, "css");
        if ($result) {
            file_put_contents($path, $result);
            return $tag;
        }

        return null;
    }

    /**
     * Compile scripts and return in a script tag
     *
     * @param  Array   $assets
     * @param  String  $name
     * @param  String  $path
     * @param  Float   $cache
     * @param  Boolean $version
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

        $result = $this->compile($assets, "js");
        if ($result) {
            file_put_contents($path, $result);
            return $tag;
        }

        return null;
    }

    /**
     * Echo tags for scripts and styles
     *
     * @param  Array   $assets
     * @param  String  $name
     * @param  String  $path
     * @param  Float   $cache
     * @param  Boolean $version
     * @return void
     */
    public function style_and_script($assets, $name, $path="", $cache=0, $version=false) {
        echo $this->script($assets, $name, $path, $cache, $version);
        echo $this->style($assets, $name, $path, $cache, $version);
    }


    /**
     * Compile assets into one file
     *
     * @param  Array  $assets
     * @param  String $type   js or css
     * @return String
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

            $ext     = $asset['ext'];
            $content = '';

            if (strpos($file, ':') !== false && $____file = $this->app->path($file)) {
                $asset['file'] = $file = $____file;
            }

            if($ext!=$type) continue;

            switch ($asset['ext']) {

                case 'js':

                    $content = @file_get_contents($file);
                    break;

                case 'css':

                    $content = @file_get_contents($file);
                    $content = $rewriteCssUrls($content, $asset);

                    break;

                default:
                    continue;
            }
            
            // Remove references to source maps
            $content = preg_replace('~/[/|\*]# sourceMappingURL=.*~', '', $content);

            $output[] = $content;
        }

        // Add newlines between files to fix problem with stacking comments.
        return implode("\n", $output);
    }

}
