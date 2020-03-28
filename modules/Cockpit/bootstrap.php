<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Helpers

$this->helpers['revisions']  = 'Cockpit\\Helper\\Revisions';
$this->helpers['updater']    = 'Cockpit\\Helper\\Updater';
$this->helpers['async']      = 'Cockpit\\Helper\\Async';
$this->helpers['jobs']       = 'Cockpit\\Helper\\Jobs';

// API
$this->module('cockpit')->extend([

    'markdown' => function($content, $extra = false) use($app) {

        static $parseDown;
        static $parsedownExtra;

        if (!$extra && !$parseDown)      $parseDown      = new \Parsedown();
        if ($extra && !$parsedownExtra)  $parsedownExtra = new \ParsedownExtra();

        return $extra ? $parsedownExtra->text($content) : $parseDown->text($content);
    },

    'clearCache' => function() use($app) {

        $dirs = ['#cache:','#tmp:','#thumbs:', '#pstorage:tmp'];

        foreach (array_unique($dirs) as &$dir) {
            $dir = $this->app->path($dir);
        }

        foreach ($dirs as $dir) {

            $path = $app->path($dir);
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {

                if (!$file->isFile()) continue;
                if (preg_match('/(\.gitkeep|\.gitignore|index\.html)$/', $file)) continue;

                @unlink($file->getRealPath());
            }

            $app->helper('fs')->removeEmptySubFolders($path);
        }

        $app->trigger('cockpit.clearcache');

        $size = 0;

        foreach ($dirs as $dir) {
            $size += $app->helper('fs')->getDirSize($dir);
        }

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        
        return ['size'=>$app->helper('utils')->formatSize($size)];
    },

    'loadApiKeys' => function() {

        $keys      = [ 'master' => '', 'special' => [] ];
        $container = $this->app->path('#storage:').'/api.keys.php';

        if (file_exists($container)) {
            
            $data = include($container);
            $data = @unserialize($this->app->decode($data, $this->app['sec-key']));

            if ($data !== false) {
                $keys = array_merge($keys, $data);
            }

        } else {
            $keys = $this->app->storage->getKey('cockpit', 'api_keys', $keys);
        }

        return $keys;
    },

    'saveApiKeys' => function($data) {

        $data = array_merge([ 'master' => '', 'special' => [] ], (array)$data);

        $this->app->storage->setKey('cockpit', 'api_keys', $data);

        // cache
        $serialized = serialize($data);
        $export     = var_export($this->app->encode($serialized, $this->app["sec-key"]), true);
        $container  = $this->app->path('#storage:').'/api.keys.php';

        return $this->app->helper('fs')->write($container, "<?php\n return {$export};");
    },

    /**
     * Generate thumbnail
     * @param array $options {
     *   @var string [cachefolder=thumbs://] - Cache folder
     *   @var string $source - Source file path
     *   @var string [$mode=thumbnail] - One of thumbnail|bestFit|resize|fitToWidth|fitToHeight
     *   @var string [$fp] - Position
     *   @var array [$filters] - Associative array of filters and it's options: ['sepia', 'sharpen']
     *   @var integer [$width] - Output width
     *   @var integer [$height] - Output height
     *   @var integer [$quality=100] - Output quality
     *   @var boolean [$rebuild=false] - Force image rebuild
     *   @var boolean [$base64=false] - Base64 output
     *   @var boolean [$output=false] - Echo response and exit application
     * }
     * @return string URL to file or Base64 output
     */
    'thumbnail' => function($options) {

        $options = array_merge([
            'cachefolder' => 'thumbs://',
            'src' => '',
            'mode' => 'thumbnail',
            'fp' => null,
            'filters' => [],
            'width' => false,
            'height' => false,
            'quality' => 100,
            'rebuild' => false,
            'base64' => false,
            'output' => false,
            'redirect' => false,
        ], $options);

        extract($options);

        if (!$width && !$height) {
            return ['error' => 'Target width or height parameter is missing'];
        }

        if (!$src) {
            return ['error' => 'Missing src parameter'];
        }

        $src   = str_replace('../', '', rawurldecode($src));
        $asset = null;

        // is asset?
        if (strpos($src, $this->app->filestorage->getUrl('assets://')) === 0) {

            $path = trim(str_replace(rtrim($this->app->filestorage->getUrl('assets://'), '/'), '', $src), '/');

            try {

                if ($this->app->filestorage->has('assets://'.$path)) {

                    $asset = $this->app->storage->findOne('cockpit/assets', ['path' => "/{$path}"]);

                    if (!$asset) {
                        $asset = ['path' => "/{$path}"];
                    }

                } else {
                    return $src;
                }

            } catch (\Exception $e) {
                return $src;
            }

        } elseif (!preg_match('/\.(png|jpg|jpeg|gif|svg)$/i', $src)) {
            $asset = $this->app->storage->findOne('cockpit/assets', ['_id' => $src]);
        }

        if ($asset) {

            $asset['path'] = trim($asset['path'], '/');
            $src = $this->app->path("#uploads:{$asset['path']}");

            if (!$src && $this->app->filestorage->has('assets://'.$asset['path'])) {

                $stream = $this->app->filestorage->readStream('assets://'.$asset['path']);

                if ($stream) {
                   $this->app->filestorage->writeStream('uploads://'.$asset['path'], $stream);
                   $src = $this->app->path("#uploads:{$asset['path']}");
                }
            }

            if ($src) {
                $src = str_replace(COCKPIT_SITE_DIR, '', $src);
            }

            if (isset($asset['fp']) && !$fp) {
                $fp = $asset['fp']['x'].' '.$asset['fp']['y'];
            }
        }

        if ($src) {

            $path = trim(str_replace(rtrim($this->app->filestorage->getUrl('site://'), '/'), '', $src), '/');

            if (file_exists(COCKPIT_SITE_DIR.'/'.$path)) {
                $src = COCKPIT_SITE_DIR.'/'.$path;
            } elseif (file_exists(COCKPIT_DOCS_ROOT.'/'.$path)) {
                $src = COCKPIT_DOCS_ROOT.'/'.$path;
            }
        }

        $path  = $this->app->path($src);
        $ext   = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (!file_exists($path) || is_dir($path)) {
            return false;
        }

        // handle svg files
        if ($ext == 'svg') {

            if ($base64) {
                return 'data:image/svg+xml;base64,'.base64_encode(file_get_contents($path));
            }

            if ($output) {
                header('Content-Type: image/svg+xml');
                header('Content-Length: '.filesize($path));
                echo file_get_contents($path);
                $this->app->stop();
            }

            return $this->app->pathToUrl($path, true);
        }

        if (!in_array($ext, ['png','jpg','jpeg','gif'])) {
            return $this->app->pathToUrl($path, true);
        }

        if (!$width || !$height) {

            list($w, $h, $type, $attr)  = getimagesize($path);

            if (!$width) $width = ceil($w * ($height/$h));
            if (!$height) $height = ceil($h * ($width/$w));
        }

        if (is_null($width) && is_null($height)) {
            return $this->app->pathToUrl($path, true);
        }

        if (!$fp) {
            $fp = 'center';
        }

        if (!in_array($mode, ['thumbnail', 'bestFit', 'resize','fitToWidth','fitToHeight'])) {
            $mode = 'thumbnail';
        }

        $method = $mode == 'crop' ? 'thumbnail' : $mode;

        $filetime = filemtime($path);
        $hash = md5($path.json_encode($options))."_{$width}x{$height}_{$quality}_{$filetime}_{$mode}_".md5($fp).".{$ext}";
        $thumbpath = $cachefolder."/{$hash}";

        if ($rebuild || !$this->app->filestorage->has($thumbpath)) {

            try {

                if ($rebuild && $this->app->filestorage->has($thumbpath)) {
                    $this->app->filestorage->delete($thumbpath);
                }

                $img = $this->app->helper("image")->take($path)->{$method}($width, $height, $fp);

                $_filters = [
                    'blur', 'brighten',
                    'colorize', 'contrast',
                    'darken', 'desaturate',
                    'edgeDetect', 'emboss',
                    'flip', 'invert', 'opacity', 'pixelate', 'sepia', 'sharpen', 'sketch'
                ];

                // Apply single filter
                foreach ($_filters as $f) {

                    if (isset($options[$f])) {
                        $img->{$f}($options[$f]);
                    }
                }

                // Apply multiple filters
                foreach ($filters as $filterName => $filterOptions) {
                    // Handle non-associative array
                    if (is_int($filterName)) {
                        $filterName = $filterOptions;
                        $filterOptions = [];
                    }

                    if (in_array($filterName, $_filters)) {
                        call_user_func_array([$img, $filterName], (array) $filterOptions);
                    }
                }

                $this->app->filestorage->write($thumbpath, $img->toString(null, $quality));

                unset($img);

            } catch(Exception $e) {
                return "data:image/gif;base64,R0lGODlhAQABAJEAAAAAAP///////wAAACH5BAEHAAIALAAAAAABAAEAAAICVAEAOw=="; // transparent 1px gif
            }
        }

        if ($base64) {
            return "data:image/{$ext};base64,".base64_encode($this->app->filestorage->read($thumbpath));
        }

        if ($output) {

            if ($output == 'redirect') {
                $this->app->reroute($this->app->filestorage->getURL($thumbpath));
            }

            header("Content-Type: image/{$ext}");
            header('Content-Length: '.$this->app->filestorage->getSize($thumbpath));
            echo $this->app->filestorage->read($thumbpath);
            $this->app->stop();
        }

        if ($redirect) {
            $this->app->reroute($this->app->filestorage->getURL($thumbpath));
        }

        return $this->app->filestorage->getURL($thumbpath);
    }
]);


// Additional module Api
include_once(__DIR__.'/module/auth.php');
include_once(__DIR__.'/module/assets.php');


// ADMIN
if (COCKPIT_ADMIN_CP) {

    include_once(__DIR__.'/admin.php');

    $this->bind('/cockpit-api.js', function() {

        $token = $this->param('token', '');
        $this->response->mime = 'js';

        $apiurl = ($this->request->is('ssl') ? 'https':'http').'://';

        if (!in_array($this->registry['base_port'], ['80', '443'])) {
            $apiurl .= $this->registry['base_host'].":".$this->registry['base_port'];
        } else {
            $apiurl .= $this->registry['base_host'];
        }

        $apiurl .= $this->routeUrl('/api');

        return $this->view('cockpit:views/api.js', compact('token', 'apiurl'));
    });
}

// CLI
if (COCKPIT_CLI) {
    $this->path('#cli', __DIR__.'/cli');
}

// WEBHOOKS
if (!defined('COCKPIT_INSTALL')) {
    include_once(__DIR__.'/webhooks.php');
}

// REST
if (COCKPIT_API_REQUEST) {

    // INIT REST API HANDLER
    include_once(__DIR__.'/rest-api.php');

    $this->on('cockpit.rest.init', function($routes) {
        $routes['cockpit'] = 'Cockpit\\Controller\\RestApi';
    });
}
