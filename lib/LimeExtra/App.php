<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

        $settings["helpers"]  = \array_merge([
            'acl'     => 'LimeExtra\\Helper\\SimpleAcl',
            'assets'  => 'LimeExtra\\Helper\\Assets',
            'fs'      => 'LimeExtra\\Helper\\Filesystem',
            'image'   => 'LimeExtra\\Helper\\Image',
            'i18n'    => 'LimeExtra\\Helper\\I18n',
            'utils'   => 'LimeExtra\\Helper\\Utils',
            'coockie' => 'LimeExtra\\Helper\\Cookie',
            'yaml'    => 'LimeExtra\\Helper\\YAML',
        ], $settings['helpers'] ?? []);

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


                $content = \preg_replace_callback('/\B@(\w+)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', function($match) use($replace) {

                    if (isset($match[3]) && \trim($match[1]) && isset($replace[$match[1]])) {
                        return \str_replace('(expr)', $match[3], $replace[$match[1]]);
                    }

                    return $match[0];

                }, $content);


                return $content;
            });

            return $renderer;
        });

        if ($this->retrieve('session.init', true)) {
            $this('session')->init();
        }
    }


    /**
    * Render view.
    * @param  String $template Path to view
    * @param  Array  $slots   Passed variables
    * @return String               Rendered view
    */
    public function view($template, $slots = []) {

        $this->trigger('app.render.view', [&$template, &$slots]);

        if (\is_string($template) && $template) {
            $this->trigger("app.render.view/{$template}", [&$template, &$slots]);
        }

        $renderer     = $this->renderer;
        $olayout      = $this->layout;

        $slots        = \array_merge($this->viewvars, $slots);
        $layout       = $olayout;

        $this->layout = false;

        if (\strpos($template, ' with ') !== false ) {
            list($template, $layout) = \explode(' with ', $template, 2);
        }

        if (\strpos($template, ':') !== false && $file = $this->path($template)) {
            $template = $file;
        }

        $slots['extend'] = function($from) use(&$layout) {
            $layout = $from;
        };

        if (!\file_exists($template)) {
            return "Couldn't resolve {$template}.";
        }

        $output = $renderer->file($template, $slots);

        if ($layout) {

            if (\strpos($layout, ':') !== false && $file = $this->path($layout)) {
                $layout = $file;
            }

            if(!\file_exists($layout)) {
                return "Couldn't resolve {$layout}.";
            }

            $slots['content_for_layout'] = $output;

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

    public function assets($src, $version=false){

        $list   = [];
        $js     = [];
        $debug  = $this->retrieve('debug');
        $jshash = '';

        foreach ((array)$src as $asset) {

            $src = $asset;

            if (\is_array($asset)) {
                extract($asset);
            }

            if (@\substr($src, -3) == '.js') {

                $ispath = \strpos($src, ':') !== false && !\preg_match('#^(|http\:|https\:)//#', $src);

                if (!$debug && $ispath && $path = $this->path($src)) {
                    $js[] = $path;
                    $jshash = md5($jshash.md5_file($path));
                } else {
                    $list[] = $this->script($asset, $version);
                }

            } elseif (@\substr($src, -4) == '.css') {
                $list[] = $this->style($asset, $version);
            }
        }

        if (count($js)) {
            
            $path = '#pstorage:tmp/'.$jshash.'.js';

            if (!$this->path($path)) {
                $contents = [];
                foreach ($js as $p) {$contents[] = file_get_contents($p); }
                $this->helper('fs')->write($path, implode("\n", $contents));
            }

            $url = $this->pathToUrl($path);
            $list[] = '<script src="'.($url.($version ? "?ver={$version}":'')).'" type="text/javascript"></script>';
        }

        return \implode("\n", $list);
    }
}
