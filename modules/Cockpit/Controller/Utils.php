<?php

namespace Cockpit\Controller;

class Utils extends \Cockpit\AuthController {

    public function thumb_url() {

        $src    = $this->param('src', false);
        $width  = $this->param('w', null);
        $height = $this->param('h', null);
        $mode   = $this->param('m', 'crop');

        if ($src) {

            $src = rawurldecode($src);

            // check if absolute url
            if (substr($src, 0,1) == '/' && file_exists($this->app['docs_root'].$src)) {
                $src = $this->app['docs_root'].$src;
            }

            $options = array(
                "rebuild"     => false,
                "cachefolder" => '#thumbs:',
                "quality"     => 100,
                "base64"      => false,
                "mode"        => $mode,
                "domain"      => false
            );

            extract($options);

            $path  = $this->app->path($src);
            $ext   = pathinfo($path, PATHINFO_EXTENSION);
            $url   = "data:image/gif;base64,R0lGODlhAQABAJEAAAAAAP///////wAAACH5BAEHAAIALAAAAAABAAEAAAICVAEAOw=="; // transparent 1px gif

            if (!file_exists($path) || is_dir($path)) {
                return false;
            }

            if (!in_array(strtolower($ext), array('png','jpg','jpeg','gif'))) {
                return $url;
            }

            if (is_null($width) && is_null($height)) {
                return $this->app->pathToUrl($path);
            }

            if (!in_array($mode, ['crop', 'best_fit', 'resize','fit_to_width'])) {
                $mode = 'crop';
            }

            $method = $mode == 'crop' ? 'thumbnail':$mode;

            if ($base64) {

                try {
                    $data = $this->app->helper("image")->take($path)->{$method}($width, $height)->base64data(null, $quality);
                } catch(Exception $e) {
                    return $url;
                }

                $url = $data;

            } else {

                $filetime = filemtime($path);
                $savepath = $this->app->path($cachefolder)."/".md5($path)."_{$width}x{$height}_{$quality}_{$filetime}_{$mode}.{$ext}";

                if ($rebuild || !file_exists($savepath)) {

                    try {
                        $this->app->helper("image")->take($path)->{$method}($width, $height)->toFile($savepath, null, $quality);
                    } catch(Exception $e) {
                        return $url;
                    }
                }

                $url = $this->app->pathToUrl($savepath);

                if ($domain) {
                    $url = rtrim($this->app->getSiteUrl(true), '/').$url;
                }

                return $url;
            }
        }

        return false;
    }
}
