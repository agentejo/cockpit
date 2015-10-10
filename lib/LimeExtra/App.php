<?php

namespace LimeExtra;

/**
 * Class App
 * @package LimeExtra
 */
class App extends \Lime\App {

    /**
     * @param array $settings
     */
    public function __construct ($settings = []) {

        $settings["helpers"]  = array_merge([
            "acl"     => "Lime\\Helper\\SimpleAcl",
            "assets"  => "Lime\\Helper\\Assets",
            "fs"      => "Lime\\Helper\\Filesystem",
            "image"   => "Lime\\Helper\\Image",
            "i18n"    => "Lime\\Helper\\I18n",
            "utils"   => "Lime\\Helper\\Utils",
            "coockie" => "Lime\\Helper\\Cookie",
            "yaml" => "Lime\\Helper\\YAML",
        ], isset($settings["helpers"]) ? $settings["helpers"] : []);

        parent::__construct($settings);

        // renderer service
        $this->service('renderer', function() {

            $renderer = new \Lexy();

            //register app helper functions
            $renderer->extend(function($content){

                $replace = [
                    'extend'   => '<?php $extend(expr); ?>',
                    'base'     => '<?php $app->base(expr); ?>',
                    'route'    => '<?php $app->route(expr); ?>',
                    'trigger'  => '<?php $app->trigger(expr); ?>',
                    'assets'   => '<?php echo $app->assets(expr); ?>',
                    'start'    => '<?php $app->start(expr); ?>',
                    'end'      => '<?php $app->end(expr); ?>',
                    'block'    => '<?php $app->block(expr); ?>',
                    'url'      => '<?php echo $app->pathToUrl(expr); ?>',
                    'view'     => '<?php echo $app->view(expr); ?>',
                    'render'   => '<?php echo $app->view(expr); ?>',
                    'include'  => '<?php echo include($app->path(expr)); ?>',
                    'lang'     => '<?php echo $app("i18n")->get(expr); ?>',
                ];


                $content = preg_replace_callback('/\B@(\w+)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', function($match) use($replace) {

                    if (isset($match[3]) && trim($match[1]) && isset($replace[$match[1]])) {
                        return str_replace('(expr)', $match[3], $replace[$match[1]]);
                    }

                    return $match[0];

                }, $content);


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
     * @param $template
     * @param array $slots
     */
    public function renderView($template, $slots = []) {
        echo $this->view($template, $slots);
    }
}
