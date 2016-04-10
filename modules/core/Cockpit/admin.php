<?php


/**
 * Helpers
 */

// because auto-load not ready yet
include(__DIR__.'/Helper/Admin.php');

$app->helpers['admin']  = 'Cockpit\\Helper\\Admin';


// ACL
$app('acl')->addResource('cockpit', [
    'manage.backups',
    'manage.media',
]);


// init acl groups + permissions + settings

$app('acl')->addGroup('admin', true);

if ($user = $app->module('cockpit')->getUser()) {

    $aclsettings = $app->retrieve('config/acl', []);

    /*
    acl:
        author:
            $admin: false
            $vars:
                mediapath: /upload
            cockpit:
                manage.media: true
                manage.backups: true

    */

    foreach ($aclsettings as $group => $settings) {

        $isSuperAdmin = $settings === true || (isset($settings['$admin']) && $settings['$admin']);
        $vars         = isset($settings['$vars']) ? $settings['$vars'] : [];

        $app('acl')->addGroup($group, $isSuperAdmin, $vars);

        if (!$isSuperAdmin && is_array($settings)) {

            foreach ($settings as $resource => $actions) {

                if ($resource == '$vars' || $resource == '$admin') continue;

                foreach ((array)$actions as $action => $allow) {
                    if ($allow) $app('acl')->allow($group, $resource, $action);
                }
            }
        }
    }
}

// init + load i18n
$app('i18n')->locale = 'en';

if ($user = $app->module('cockpit')->getUser()) {

    $locale = isset($user['i18n']) ? $user['i18n'] : $app->retrieve('i18n', 'en');

    if ($translationspath = $app->path("#config:cockpit/i18n/{$locale}.php")) {
        $app('i18n')->locale = $locale;
        $app('i18n')->load($translationspath, $locale);
    }
}

$app->bind('/cockpit.i18n.data', function() {
    $this->response->mime = 'js';
    $data = $this('i18n')->data($this('i18n')->locale);
    return 'if (i18n) { i18n.register('.(count($data) ? json_encode($data):'{}').'); }';
});


/**
 * register assets
 */

$assets = [

    'assets:polyfills/es6-shim.js',
    'assets:polyfills/dom4.js',
    'assets:polyfills/object-observe.js',
    'assets:lib/jquery.js',
    'assets:lib/lodash.js',
    'assets:lib/riot/riot.js',
    'assets:lib/riot/riot.bind.js',
    'assets:lib/riot/riot.view.js',
    'assets:lib/uikit/js/uikit.min.js',
    'assets:lib/uikit/js/components/notify.min.js',
    'assets:lib/uikit/js/components/tooltip.min.js',
    'assets:lib/uikit/js/components/lightbox.min.js',
    'assets:lib/uikit/js/components/sortable.min.js',
    'assets:lib/uikit/js/components/sticky.min.js',
    'assets:lib/mousetrap.js',
    'assets:lib/storage.js',
    'assets:lib/i18n.js',
    'assets:app/js/app.js',
    'assets:app/js/app.utils.js',
    'cockpit:assets/components.js',
    'cockpit:assets/cockpit.js',

    'assets:app/css/style.css',
];

// load custom css style
if ($app->path('config:cockpit/style.css')) {
    $assets[] = 'config:cockpit/style.css';
}

$app['app.assets.base'] = $assets;


/**
 * register routes
 */

$app->bind('/', function(){

    if ($this['cockpit.start'] && $this->module('cockpit')->getUser()) {
        $this->reroute($this['cockpit.start']);
    }

    return $this->invoke('Cockpit\\Controller\\Base', 'dashboard');
});

$app->bindClass('Cockpit\\Controller\\Utils', 'cockpit/utils');
$app->bindClass('Cockpit\\Controller\\Base', 'cockpit');
$app->bindClass('Cockpit\\Controller\\Settings', 'settings');
$app->bindClass('Cockpit\\Controller\\Accounts', 'accounts');
$app->bindClass('Cockpit\\Controller\\Auth', 'auth');
$app->bindClass('Cockpit\\Controller\\Media', 'media');
$app->bindClass('Cockpit\\Controller\\Assets', 'assetsmanager');
$app->bindClass('Cockpit\\Controller\\RestAdmin', 'restadmin');


/**
 * on admint init
 */
$app->on('admin.init', function() {

    // bind finder
    $this->bind('/finder', function() {

        $this->layout = 'cockpit:views/layouts/app.php';
        $this["user"] = $this->module('cockpit')->getUser();
        return $this->view('cockpit:views/base/finder.php');

    }, $this->module("cockpit")->hasaccess('cockpit', 'manage.media'));

}, 0);


/**
 * listen to app search to filter accounts
 */

$app->on('cockpit.search', function($search, $list) {

    if (!$this->module('cockpit')->userInGroup('admin')) {
        return;
    }

    foreach ($this->storage->find('cockpit/accounts') as $a) {

        if (strripos($a['name'].' '.$a['user'], $search)!==false){
            $list[] = [
                'icon'  => 'user',
                'title' => $a['name'],
                'url'   => $this->routeUrl('/accounts/account/'.$a['_id'])
            ];
        }
    }
});

// dashboard widgets


$app->on("admin.dashboard.widgets", function($widgets) {

    $title   = $this("i18n")->get("Today");

    $widgets[] = [
        "name"    => "time",
        "content" => $this->view("cockpit:views/widgets/datetime.php", compact('title')),
        "area"    => 'main'
    ];

}, 100);

/**
 * handle error pages
 */
$app->on("after", function() {

    switch ($this->response->status) {
        case 500:

            if ($this['debug']) {

                if ($this->req_is('ajax')) {
                    $this->response->body = json_encode(['error' => json_decode($this->response->body, true)]);
                } else {
                    $this->response->body = $this->render("cockpit:views/errors/500-debug.php", ['error' => json_decode($this->response->body, true)]);
                }

            } else {

                if ($this->req_is('ajax')) {
                    $this->response->body = '{"error": "500", "message": "system error"}';
                } else {
                    $this->response->body = $this->view("cockpit:views/errors/500.php");
                }
            }

            $this->trigger("cockpit.request.error", ['500']);
            break;

        case 404:

            if ($this->req_is('ajax')) {
                $this->response->body = '{"error": "404", "message":"File not found"}';
            } else {
                $this->response->body = $this->view("cockpit:views/errors/404.php");
            }

            $this->trigger("cockpit.request.error", ['404']);
            break;
    }
});

// load package info
$app['cockpit'] = json_decode($app('fs')->read('#root:package.json'), true);

// init app helper
$app('admin')->init();
