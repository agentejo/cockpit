<?php

// ACL
$this("acl")->addResource('Collections', ['manage.collections', 'manage.entries']);

$app->on('admin.init', function() {

    if (!$this->module('auth')->hasaccess('Collections', ['manage.collections', 'manage.entries'])) return;

    // bind controllers
    $this->bindClass('Collections\\Controller\\Collections', 'collections');
    $this->bindClass('Collections\\Controller\\Api', 'api/collections');

    $this('admin')->menu('top', [
        'url'    => $this->routeUrl('/collections'),
        'label'  => '<i class="uk-icon-list"></i>',
        'title'  => $this('i18n')->get('Collections'),
        'active' => (strpos($this['route'], '/collections') === 0)
    ], 5);

    // handle global search request
    $this->on('cockpit.globalsearch', function($search, $list) {

        foreach ($this->db->find('common/collections') as $c) {
            if (stripos($c['name'], $search)!==false){
                $list[] = [
                    'title' => '<i class="uk-icon-list"></i> '.$c['name'],
                    'url'   => $this->routeUrl('/collections/entries/'.$c['_id'])
                ];
            }
        }
    });

});

$app->on('admin.dashboard.aside', function() {

    if (!$this->module('auth')->hasaccess('Collections', ['manage.collections', 'manage.entries'])) return;

    $title       = $this('i18n')->get('Collections');
    $badge       = $this->db->getCollection('common/collections')->count();
    $collections = $this->db->find('common/collections', ['limit'=> 3, 'sort'=>['created'=>-1] ])->toArray();

    $this->renderView('collections:views/dashboard.php with cockpit:views/layouts/dashboard.widget.php', compact('title', 'badge', 'collections'));
});


// register content fields
$app->on("cockpit.content.fields.sources", function() {

    echo $this->assets([
        'collections:assets/field.linkcollection.js',
    ], $this['cockpit/version']);

});
