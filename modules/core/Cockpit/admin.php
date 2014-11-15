<?php

// ACL
$app("acl")->addResource("Cockpit", ['manage.backups']);

$app["cockpit"] = json_decode($app->helper("fs")->read("#root:package.json"), true);

$assets = array_merge([

    // misc
    'assets:vendor/mousetrap.js',

    // angular
    'assets:vendor/angular/angular.min.js',
    'assets:vendor/angular/angular-sanitize.min.js',

    // uikit addons
    'assets:vendor/uikit/js/components/autocomplete.min.js',
    'assets:vendor/uikit/js/components/search.min.js',
    'assets:vendor/uikit/js/components/form-select.min.js',
    'assets:vendor/multipleselect.js',

    // app related
    'assets:js/app.js',
    'assets:js/app.module.js',
    'assets:js/bootstrap.js',

], $app->retrieve('app.config/app.assets.backend', []));

$app['app.assets.backend'] = $assets;

// helpers
$app->helpers["admin"]    = 'Cockpit\\Helper\\Admin';
$app->helpers["versions"] = 'Cockpit\\Helper\\Versions';
$app->helpers["history"]  = 'Cockpit\\Helper\\HistoryLogger';

$app->bind("/", function(){
    return $this->invoke("Cockpit\\Controller\\Base", "dashboard");
});

$app->bind("/dashboard", function(){
    return $this->invoke("Cockpit\\Controller\\Base", "dashboard");
});

$app->bind("/settingspage", function(){
    return $this->invoke("Cockpit\\Controller\\Base", "settings");
});

$app->bindClass("Cockpit\\Controller\\Settings", "settings");

//global search
$app->bind("/cockpit-globalsearch", function(){

    $query = $this->param("search", false);
    $list  = new \ArrayObject([]);

    if ($query) {
        $this->trigger("cockpit.globalsearch", [$query, $list]);
    }

    return json_encode(["results"=>$list->getArrayCopy()]);
});

// dashboard widgets
$app->on("admin.dashboard.main", function() {
    $title = $this("i18n")->get("Today");
    $this->renderView("cockpit:views/dashboard/datetime.php with cockpit:views/layouts/dashboard.widget.php", compact('title'));
}, 100);

$app->on("admin.dashboard.main", function() {
    $this->renderView("cockpit:views/dashboard/history.php", ['history' => $this("history")->load()]);
}, 5);


// init admin menus
$app['admin.menu.top']      = new \PriorityQueue();
$app['admin.menu.dropdown'] = new \PriorityQueue();


// load i18n definition
if ($user = $app("session")->read('cockpit.app.auth', null)) {
    $app("i18n")->locale = isset($user['i18n']) ? $user['i18n'] : $app("i18n")->locale;
}

$locale = $app("i18n")->locale;

$app("i18n")->load("custom:i18n/{$locale}.php", $locale);

$app->bind("/i18n-js", function() use($locale){

    $this->response->mime = "js";
    $data = $this("i18n")->data($locale);

    return 'if (i18n) { i18n.register('.(count($data) ? json_encode($data):'{}').'); }';
});

// register content fields
$app->on("cockpit.content.fields.sources", function() {

    echo $this->assets([
        'assets:js/angular/fields/contentfield.js',
        'assets:js/angular/fields/codearea.js',
        'assets:js/angular/fields/wysiwyg.js',
        'assets:js/angular/fields/gallery.js',
        'assets:js/angular/fields/tags.js',
        'assets:js/angular/fields/location.js',
        'assets:js/angular/fields/multifield.js',
        'collections:assets/field.linkcollection.js',
        'mediamanager:assets/field.pathpicker.js',
        'assets:js/angular/directives/mediapreview.js',
        'assets:js/angular/fields/htmleditor.js'
    ], $this['cockpit/version']);

}, 100);
