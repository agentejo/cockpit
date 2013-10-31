<?php


// API

$this->module("mediamanager")->thumbnail = function($image, $width, $height, $options=array()) use($app) {

    $options = array_merge(array(
        "rebuild"     => false,
        "cachefolder" => "cache:thumbs",
        "quality"     => 100
    ), $options);

    extract($options);

    $path  = $app->path($image);
    $ext   = pathinfo($path, PATHINFO_EXTENSION);
    $url   = "data:image/gif;base64,R0lGODlhAQABAJEAAAAAAP///////wAAACH5BAEHAAIALAAAAAABAAEAAAICVAEAOw=="; // transparent 1px gif

    if(!file_exists($path) || is_dir($path)) {
        return $url;
    }

    if(!in_array(strtolower($ext), array('png','jpg','jpeg','gif'))) {
        return $url;
    }

    $filetime = filemtime($path);
    $savepath = $app->path($cachefolder)."/".md5($path)."_{$width}x{$height}_{$quality}_{$filetime}.{$ext}";

    if($rebuild || !file_exists($savepath)) {
        try {
            $app("image")->take($path)->thumbnail($width, $height)->save($savepath, $quality);
        } catch(Exception $e) {
            return $url;
        }
    }

    $url = $app->pathToUrl($savepath);

    return $url;
};

if (!function_exists('thumbnail_url')) {

    function thumbnail_url($image, $width, $height, $options=array()) {
        return c("mediamanager")->thumbnail($image, $width, $height, $options);
    }
}

if (!function_exists('thumbnail')) {

    function thumbnail($image, $width, $height, $options=array()) {

        $url =  c("mediamanager")->thumbnail($image, $width, $height, $options=array());

        echo '<img src="'.$url.'" alt="'.$url.'">';
    }
}


if(COCKPIT_ADMIN) {


    // bind controllers
    foreach (array('Mediamanager') as $controller) {
        $app->bindClass("Mediamanager\\Controller\\{$controller}", strtolower($controller));
    }

    $app->on("app.layout.header", function() use($app){
    ?>
        <script>window.COCKPIT_UPLOADS_BASE_URL = '<?=$app->pathToUrl("uploads:");?>';</script>
    <?php
    });


    $app->on("admin.init", function() use($app){

        $app("admin")->menu("top", [
            "url"   => $app->routeUrl("/mediamanager"),
            "label" => '<i class="uk-icon-cloud"></i>',
            "title" => "Mediamanager"
        ], 1);
    });

}