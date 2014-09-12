<?php


// API

$this->module("mediamanager")->extend([

    'thumbnail' => function($image, $width = null, $height = null, $options=array()) use($app) {

        if ($width && is_array($height)) {
            $options = $height;
            $height  = $width;
        } else {
            $height = $height ?: $width;
        }

        $options = array_merge(array(
            "rebuild"     => false,
            "cachefolder" => "cache:thumbs",
            "quality"     => 100,
            "base64"      => false,
            "mode"        => "crop",
            "domain"      => false
        ), $options);

        extract($options);

        $path  = $app->path($image);
        $ext   = pathinfo($path, PATHINFO_EXTENSION);
        $url   = "data:image/gif;base64,R0lGODlhAQABAJEAAAAAAP///////wAAACH5BAEHAAIALAAAAAABAAEAAAICVAEAOw=="; // transparent 1px gif

        if (!file_exists($path) || is_dir($path)) {
            return $url;
        }

        if (!in_array(strtolower($ext), array('png','jpg','jpeg','gif'))) {
            return $url;
        }

        if (!is_numeric($height)) {
            $height = $width;
        }

        if (is_null($width) && is_null($height)) {
            return $app->pathToUrl($path);
        }

        if (!in_array($mode, ['crop', 'best_fit', 'resize'])) {
            $mode = 'crop';
        }

        $method = $mode == 'crop' ? 'thumbnail':$mode;

        if ($base64) {

            try {
                $data = $app("image")->take($path)->{$method}($width, $height)->base64data(null, $quality);
            } catch(Exception $e) {
                return $url;
            }

            $url = $data;

        } else {

            $filetime = filemtime($path);
            $savepath = $app->path($cachefolder)."/".md5($path)."_{$width}x{$height}_{$quality}_{$filetime}_{$mode}.{$ext}";

            if ($rebuild || !file_exists($savepath)) {
                try {
                    $app("image")->take($path)->{$method}($width, $height)->save($savepath, $quality);
                } catch(Exception $e) {
                    return $url;
                }
            }

            $url = $app->pathToUrl($savepath);

            if ($domain) {
                $url = rtrim($app->getSiteUrl(true), '/').$url;
            }
        }

        return $url;
    },

    'thumbnails' => function ($settings = []) {

        $settings = array_merge([
            'images'  => [],
            'width'   => 50,
            'height'  => false,
            'options' => []
        ], $settings);

        $urls = [];

        foreach ($settings['images'] as $image) {

            if (is_string($image)) {

                $urls[$image] = $this->thumbnail($image, $settings['width'], $settings['height'], $settings['options']);

            } elseif ($image) {

                $image = array_merge($settings, (array) $image);

                if (isset($image['path'])) {
                    $urls[$image['path']] = $this->thumbnail($image['path'], $image['width'], $image['height'], $image['options']);
                }

            }
        }

        return $urls;
    }
]);

// extend lexy parser
$app->renderer->extend(function($content){

    $content = preg_replace('/(\s*)@thumbnail_url\((.+?)\)/', '$1<?php echo cockpit("mediamanager")->thumbnail($2); ?>', $content);
    $content = preg_replace('/(\s*)@thumbnail\((.+?)\)/', '$1<?php thumbnail($2); ?>', $content);

    return $content;
});

if (!function_exists('thumbnail_url')) {

    function thumbnail_url($image, $width = null, $height = null, $options=array()) {
        return cockpit("mediamanager")->thumbnail($image, $width, $height, $options);
    }
}

if (!function_exists('thumbnail')) {

    function thumbnail($image, $width = null, $height = null, $options=array()) {

        if ($width && is_array($height)) {
            $options = $height;
            $height  = $width;
        } else {
            $height = $height ?: $width;
        }

        $url = cockpit("mediamanager")->thumbnail($image, $width, $height, $options);

        // generate attributes list
        $attributes = \Lime\fetch_from_array($options, 'attrs', []);

        if (is_array($attributes)) {

            $tmp        = [];
            $attributes = array_merge(['alt' => $image], $attributes);

            foreach ($attributes as $key => $val) {
                $tmp[] = $key.'="'.$val.'"';
            }

            $attributes = implode(' ', $tmp);
        }

        echo '<img src="'.$url.'" '.$attributes.'>';
    }
}

// REST
$app->on("cockpit.rest.init", function($routes) {
    $routes["mediamanager"] = 'Mediamanager\\Controller\\RestApi';
});


// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_REST) include_once(__DIR__.'/admin.php');
