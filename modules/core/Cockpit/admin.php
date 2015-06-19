<?php

// ACL
$app('acl')->addResource('cockpit', [
    'manage.backups',
    'manage.media',
]);

// init acl groups + permissions
$app('acl')->addGroup('admin', true);

if ($user = $app->module('cockpit')->getUser()) {

    $aclsettings = $app->retrieve('config/acl', []);

    foreach ($aclsettings as $group => $settings) {

        $app('acl')->addGroup($group, $settings === true ? true:false);

        if (is_array($settings)) {

            foreach ($resources as $resource => $actions) {
                foreach ($actions as $action => $value) {
                    if ($value) $app('acl')->allow($group, $resource, $action);
                }
            }
        }
    }
}

// extend lexy parser
$app->renderer->extend(function($content){
    return preg_replace('/(\s*)@hasaccess\?\((.+?)\)/', '$1<?php if ($app->module("cockpit")->hasaccess($2)) { ?>', $content);
});

$app['cockpit'] = json_decode($app->helper('fs')->read('#root:package.json'), true);


$app->on('admin.init', function() {

    $this["user"] = $this->module('cockpit')->getUser();

    // bind finder
    $this->bind('/finder', function() {

        $this->layout = 'cockpit:views/layouts/app.php';

        return $this->view('cockpit:views/base/finder.php');

    }, $this->module("cockpit")->hasaccess('cockpit', 'manage.media'));

}, 0);


/**
 * register assets
 */

$app['app.assets.base'] = [

    'assets:polyfills/es6-shim.js',
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
    'assets:lib/storage.js',
    'assets:lib/i18n.js',
    'assets:app/js/app.js',
    'assets:app/js/app.utils.js',
    'cockpit:assets/components.js',
    'cockpit:assets/cockpit.js',

    'assets:app/css/style.css',
];

$app['app.assets.backend'] = new ArrayObject(array_merge($app['app.assets.base'], [

    // uikit components
    'assets:lib/uikit/js/components/autocomplete.min.js',
    'assets:lib/uikit/js/components/tooltip.min.js',

    // app related
    'assets:app/js/bootstrap.js'

], $app->retrieve('app.config/app.assets.backend', [])));

// load custom css style
if ($app->path('config:style.css')) {
    $app['app.assets.backend']->append('config:style.css');
}


/**
 * web components
 */

$app['app.assets.components'] = [];

/**
 * admin menus
 */

$app['admin.menu.modules'] = new ArrayObject([]);


/**
 * register routes
 */

$app->bind('/', function(){
    return $this->invoke('Cockpit\\Controller\\Base', 'dashboard');
});

$app->bindClass('Cockpit\\Controller\\Base', 'cockpit');
$app->bindClass('Cockpit\\Controller\\Settings', 'settings');
$app->bindClass('Cockpit\\Controller\\Accounts', 'accounts');
$app->bindClass('Cockpit\\Controller\\Auth', 'auth');
$app->bindClass('Cockpit\\Controller\\Media', 'media');

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
$app->on("admin.dashboard.main", function() {
    $title = $this("i18n")->get("Today");
    $this->renderView("cockpit:views/widgets/datetime.php", compact('title'));
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

            break;

        case 404:

            if ($this->req_is('ajax')) {
                $this->response->body = '{"error": "404", "message":"File not found"}';
            } else {
                $this->response->body = $this->view("cockpit:views/errors/404.php");
            }
            break;
    }
});
