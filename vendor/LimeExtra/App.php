<?php

namespace LimeExtra;

class App extends \Lime\App {

    public function __construct ($settings = []) {

        $settings["helpers"]  = array_merge([
            "acl"     => "Lime\\Helper\\SimpleAcl",
            "assets"  => "Lime\\Helper\\Assets",
            "fs"      => "Lime\\Helper\\Filesystem",
            "image"   => "Lime\\Helper\\Image",
            "i18n"    => "Lime\\Helper\\I18n",
            "utils"   => "Lime\\Helper\\Utils",
            "coockie" => "Lime\\Helper\\Cookie",
        ], isset($settings["helpers"]) ? $settings["helpers"] : []);

        parent::__construct($settings);

        // renderer service
        $this->service('renderer', function() {

            $renderer = new \Lexy();

            //register app helper functions
            $renderer->extend(function($content){

                $content = preg_replace('/(\s*)@extend\((.+?)\)/' , '$1<?php $extend($2); ?>', $content);
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

            return $renderer;
        });

        $this("session")->init();
    }


    /**
    * Render view.
    * @param  String $template Path to view
    * @param  Array  $slots   Passed variables
    * @return String               Rendered view
    */
    public function view($template, $slots = []) {

        $renderer     = $this->renderer;
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

        $slots['extend'] = function($from) use(&$layout) {
            $layout = $from;
        };

        if (!file_exists($template)) {
            return "Couldn't resolve {$template}.";
        }

        $output = $renderer->file($template, $slots);

        if ($layout) {

            if (strpos($layout, ':') !== false && $file = $this->path($layout)) {
                $layout = $file;
            }

            if(!file_exists($layout)) {
                return "Couldn't resolve {$layout}.";
            }

            $slots["content_for_layout"] = $output;

            $output = $renderer->file($layout, $slots);
        }

        $this->layout = $olayout;

        return $output;
    }

    /**
     * Outputs view content result
     */
    public function renderView($template, $slots = []) {
        echo $this->view($template, $slots);
    }
}
