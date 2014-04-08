<?php


// API

$this->module("mediamanager")->extend([

    "thumbnail" => function($image, $width = null, $height = null, $options=array()) use($app) {

        if($width && is_array($height)) {
            $options = $height;
            $height  = $width;
        }

        $options = array_merge(array(
            "rebuild"     => false,
            "cachefolder" => "cache:thumbs",
            "quality"     => 100,
            "base64"      => false
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

        if(!is_numeric($height)) {
            $height = $width;
        }

        if(is_null($width) && is_null($height)) {
            return $app->pathToUrl($path);
        }

        if($base64) {

            try {
                $data = $app("image")->take($path)->thumbnail($width, $height)->base64data(null, $quality);
            } catch(Exception $e) {
                return $url;
            }

            $url = $data;

        } else {

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
        }

        return $url;
    }
]);

// extend lexy parser
$app->renderer()->extend(function($content){

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

        $url = cockpit("mediamanager")->thumbnail($image, $width, $height, $options=array());

        echo '<img src="'.$url.'" alt="'.$url.'">';
    }
}

//rest
$app->on("cockpit.rest.init", function($routes) {
    $routes["mediamanager"] = 'Mediamanager\\Controller\\RestApi';
});


if(COCKPIT_ADMIN && !COCKPIT_REST) {


    $app->on("app.layout.header", function() use($app){

        $mediapath = trim($app->module("auth")->getGroupSetting("media.path", '/'), '/');

        ?>
            <script>
                window.COCKPIT_SITE_BASE_URL  = '<?=$app->pathToUrl("site:");?>';
                window.COCKPIT_MEDIA_BASE_URL = '<?=rtrim($app->pathToUrl("site:{$mediapath}"), '/');?>';
            </script>
        <?php
    });


    $app->on("admin.init", function() use($app){

        // bind controller
        $app->bindClass("Mediamanager\\Controller\\Mediamanager", "mediamanager");

        // thumbnail api
        $app->bind("/media/thumbnail/*", function($params) use($app){
            $options = $app->params("options", []);
            return $app->module("mediamanager")->thumbnail($params[":splat"], $options);
        });

        if(!$app->module("auth")->hasaccess("Mediamanager","manage")) return;

        $app("admin")->menu("top", [
            "url"    => $app->routeUrl("/mediamanager"),
            "label"  => '<i class="uk-icon-cloud"></i>',
            "title"  => $app("i18n")->get("Mediamanager"),
            "active" => (strpos($app["route"], '/mediamanager') === 0)
        ], 0);


        // handle global search request
        $app->on("cockpit.globalsearch", function($search, $list) use($app){

            $user = $app->module("auth")->getUser();

            $bookmarks = $app->memory->get("mediamanager.bookmarks.".$user["_id"], ["folders"=>[], "files"=>[]]);

            foreach ($bookmarks["folders"] as $f) {
                if(stripos($f["name"], $search)!==false){
                    $list[] = [
                        "title" => '<i class="uk-icon-folder-o"></i> '.$f["name"],
                        "url"   => $app->routeUrl('/mediamanager#'.$f["path"])
                    ];
                }
            }

            foreach ($bookmarks["files"] as $f) {
                if(stripos($f["name"], $search)!==false){
                    $list[] = [
                        "title" => '<i class="uk-icon-file-o"></i> '.$f["name"],
                        "url"   => $app->routeUrl('/mediamanager#'.dirname($f["path"]))
                    ];
                }
            }
        });

    });

    // acl
    $app("acl")->addResource("Mediamanager", ['manage']);
}