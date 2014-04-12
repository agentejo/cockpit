<?php

namespace LimeExtra;

class App extends \Lime\App {

    public $viewvars         = array();

    protected $view_renderer = null;

    public function __construct ($settings = array()) {

        $settings["helpers"]  = array_merge([
            "acl"     => "Lime\\Helper\\SimpleAcl",
            "assets"  => "Lime\\Helper\\Assets",
            "cache"   => "Lime\\Helper\\Cache",
            "fs"      => "Lime\\Helper\\Filesystem",
            "image"   => "Lime\\Helper\\Image",
            "i18n"    => "Lime\\Helper\\I18n",
            "utils"   => "Lime\\Helper\\Utils",
            "coockie" => "Lime\\Helper\\Coockie",
        ], isset($settings["helpers"]) ? $settings["helpers"] : array());

        parent::__construct($settings);

        $this->viewvars["app"]        = $this;
        $this->viewvars["base_url"]   = $this["base_url"];
        $this->viewvars["base_route"] = $this["base_route"];
        $this->viewvars["docs_root"]  = $this["docs_root"];

        $this->registry["modules"] = new \ArrayObject(array());

        $this("session")->init();
    }

    public function registerModule($name, $dir) {

        $name = strtolower($name);

        if (!isset($this->registry["modules"][$name])) {

            $this->path($name, $dir);
            $this->registry["modules"][$name] = new Module($this);
            $this->registry["modules"][$name]->_dir = $dir;

            $this->bootModule("{$dir}/bootstrap.php", $this->registry["modules"][$name]);
        }

        return $this->registry["modules"][$name];
    }

    public function loadModules($dirs) {

        $modules = [];
        $dirs    = (array)$dirs;

        foreach ($dirs as &$dir) {

            if (file_exists($dir)){

                // load modules
                foreach (new \DirectoryIterator($dir) as $module) {

                    if($module->isFile() || $module->isDot()) continue;

                    $this->registerModule($module->getBasename(), $module->getPathname());

                    $modules[] = strtolower($module);
                }

                $this["autoload"]->append($dir);

            }
        }

        return $modules;
    }

    public function module($name) {
        return $this->registry["modules"]->offsetExists($name) && $this->registry["modules"][$name] ? $this->registry["modules"][$name] : null;
    }

    protected function bootModule($bootfile, $module) {

        $app = $this;

        require($bootfile);
    }


    /**
    * Render view.
    * @param  String $template Path to view
    * @param  Array  $slots   Passed variables
    * @return String               Rendered view
    */
    public function view($template, $slots = array()) {

        $renderer     = $this->renderer();
        $olayout      = $this->layout;

        $slots         = array_merge($this->viewvars, $slots);
        $layout       = $olayout;

        $this->layout = false;

        if (strpos($template, ' with ') !== false ) {
            list($template, $layout) = explode(' with ', $template, 2);
        }

        if (strpos($template, ':') !== false && $file = $this->path($template)) {
            $template = $file;
        }

        $extend = function($from) use(&$layout) {
            $layout = $from;
        };

        if (!file_exists($template)) {
            return "Couldn't resolve {$template}.";
        }

        $cachedfile = $this->get_cached_view($template);

        if ($cachedfile) {
            $output = $this->render($cachedfile, $slots);
        } else {
            $output = $renderer->file($template, $slots);
        }


        if ($layout) {

            if (strpos($layout, ':') !== false && $file = $this->path($layout)) {
                $layout = $file;
            }

            if(!file_exists($layout)) {
                return "Couldn't resolve {$layout}.";
            }

            $slots["content_for_layout"] = $output;

            $cachedfile = $this->get_cached_view($layout);

            if($cachedfile) {
                $output = $this->render($cachedfile, $slots);
            } else {
                $output = $renderer->file($layout, $slots);
            }
        }

        $this->layout = $olayout;

        return $output;
    }

    public function renderer() {

        if (!$this->view_renderer)  {

            $this->view_renderer = new \Lexy();

            //register app helper functions
            $this->view_renderer->extend(function($content){

                $content = preg_replace('/(\s*)@base\((.+?)\)/'   , '$1<?php $app->base($2); ?>', $content);
                $content = preg_replace('/(\s*)@route\((.+?)\)/'  , '$1<?php $app->route($2); ?>', $content);
                $content = preg_replace('/(\s*)@scripts\((.+?)\)/', '$1<?php echo $app->assets($2); ?>', $content);
                $content = preg_replace('/(\s*)@render\((.+?)\)/' , '$1<?php echo $app->view($2); ?>', $content);
                $content = preg_replace('/(\s*)@trigger\((.+?)\)/', '$1<?php $app->trigger($2); ?>', $content);
                $content = preg_replace('/(\s*)@lang\((.+?)\)/'   , '$1<?php echo $app("i18n")->get($2); ?>', $content);

                $content = preg_replace('/(\s*)@start\((.+?)\)/'   , '$1<?php $app->start($2); ?>', $content);
                $content = preg_replace('/(\s*)@end\((.+?)\)/'     , '$1<?php $app->end($2); ?>', $content);
                $content = preg_replace('/(\s*)@block\((.+?)\)/'   , '$1<?php $app->block($2); ?>', $content);

                return $content;
            });
        }

        return $this->view_renderer;
    }

    protected function get_cached_view($template) {

        $cachefile   = md5($template).'.view.php';
        $cachefolder = $this->path("tmp:");

        if (!$cachefolder) {
            return false;
        }

        $cachedfile  = $this->path("tmp:{$cachefile}");

        if (!$cachedfile) {
            $cachedfile = $this->cache_template($template);
        }

        if ($cachedfile) {

            $mtime = filemtime($template);

            if(filemtime($cachedfile)!=$mtime) {
                $cachedfile = $this->cache_template($template, $mtime);
            }

            return $cachedfile;
        }

        return false;
    }

    protected function cache_template($file, $filemtime = null) {

        if (!$filemtime){
            $filemtime = filemtime($file);
        }

        $cachefile = md5($file).'.view.php';

        if (file_put_contents($this->path("tmp:").$cachefile, $this->renderer()->parse(file_get_contents($file), false, $file))){
            $cachedfile = $this->path("tmp:{$cachefile}");
            touch($cachedfile,  $filemtime);

            return $cachedfile;
        }

        return false;
    }
}