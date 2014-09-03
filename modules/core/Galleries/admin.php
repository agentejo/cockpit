<?php

// ACL
$app("acl")->addResource('Galleries', ['create.gallery', 'edit.gallery']);

$app->on('admin.init', function() {

    if (!$this->module('auth')->hasaccess('Galleries', ['create.gallery', 'edit.gallery'])) return;

    $this->bindClass('Galleries\\Controller\\Galleries', 'galleries');
    $this->bindClass('Galleries\\Controller\\Api', 'api/galleries');

    $this('admin')->menu('top', [
        'url'    => $this->routeUrl('/galleries'),
        'label'  => '<i class="uk-icon-picture-o"></i>',
        'title'  => $this('i18n')->get('Galleries'),
        'active' => (strpos($this['route'], '/galleries') === 0)
    ], 5);

    // handle global search request
    $this->on('cockpit.globalsearch', function($search, $list) {

        foreach ($this->db->find('common/galleries') as $g) {
            if (stripos($g['name'], $search)!==false){
                $list[] = [
                    'title' => '<i class="uk-icon-picture-o"></i> '.$g['name'],
                    'url'   => $this->routeUrl('/galleries/gallery/'.$g['_id'])
                ];
            }
        }
    });
});

$app->on('admin.dashboard.aside', function() {

    if (!$this->module('auth')->hasaccess('Galleries', ['create.gallery', 'edit.gallery'])) return;

    $title     = $this('i18n')->get('Galleries');
    $badge     = $this->db->getCollection('common/galleries')->count();
    $galleries = $this->db->find('common/galleries', ['limit'=> 3, 'sort'=>['created'=>-1] ])->toArray();

    $this->renderView('galleries:views/dashboard.php with cockpit:views/layouts/dashboard.widget.php', compact('title', 'badge', 'galleries'));
});
