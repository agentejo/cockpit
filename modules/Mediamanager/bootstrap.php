<?php


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