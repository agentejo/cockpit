<?php

// API

$this->module('galleries')->extend([

    'gallery' => function($name) use($app) {

        static $galleries;

        if (null === $galleries) {
            $galleries = [];
        }

        $gallery = null;

        if (!isset($galleries[$name])) {
            $galleries[$name] = $app->db->findOne('common/galleries', ['name'=>$name]);
        }

        $gallery = $galleries[$name];

        return $gallery ? $gallery['images'] : null;
    },

    'galleryById' => function($id) use($app) {

        static $galleries;

        if (null === $galleries) {
            $galleries = [];
        }

        if (!isset($galleries[$id])) {
            $galleries[$id] = $app->db->findOne('common/galleries', ['_id'=>$id]);
        }

        return $galleries[$id];
    },

    'galleries' => function($options = []) use($app) {

        return $app->db->find('common/galleries', $options)->toArray();
    },

    'group' => function($group, $sort = null) use($app) {

        return $this->galleries(['filter' =>['group' => $group], 'sort'=> $sort]);
    },

    'get_gallery_by_slug' => function($slug) use($app) {
        static $galleries;

        if (null === $galleries) {
            $galleries = [];
        }

        $gallery = null;

        if (!isset($galleries[$slug])) {
            $galleries[$slug] = $app->db->findOne('common/galleries', ['slug'=>$slug]);
        }

        $gallery = $galleries[$slug];

        return $gallery ? $gallery['images'] : null;
    },
]);


if (!function_exists('gallery')) {
    function gallery($name) {
        return cockpit('galleries')->gallery($name);
    }
}


// REST
$app->on('cockpit.rest.init', function($routes) {
    $routes["galleries"] = 'Galleries\\Controller\\RestApi';
});

// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_REST) include_once(__DIR__.'/admin.php');
