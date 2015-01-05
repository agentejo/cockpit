<?php

// API

$this->module("regions")->extend([

    'get_region' => function($name) use($app) {

        static $regions;

        if (null === $regions) {
            $regions = [];
        }

        if (!isset($regions[$name])) {
            $regions[$name] = $app->db->findOne('common/regions', ['name'=>$name]);
        }

        return $regions[$name];
    },

    'get_region_by_slug' => function($slug) use($app) {

        return $app->db->findOne('common/regions', ['slug'=>$slug]);
    },

    'render' => function($name, $params = [], $locale = null) use($app) {

        if (!$locale && is_string($params)) {
            $locale = $params;
            $params = [];
        }

        $region = $this->get_region($name);

        if (!$region) {
            return null;
        }

        $renderer = $app->renderer;
        $_fields  = isset($region['fields']) ? $region['fields'] : [];
        $fields   = ['_fields' => $_fields];

        if (count($_fields)) {
            foreach ($region['fields'] as &$field) {

                $fields[$field['name']] = $field['value'];

                if ($locale && isset($field["value_{$locale}"])) {
                    $fields[$field['name']] = $field["value_{$locale}"];
                }
            }
        }

        $fields = array_merge($fields, $params);

        $app->trigger('region_before_render', [$name, $region['tpl'], $fields]);

        $output = $renderer->execute($region['tpl'], $fields);

        $app->trigger('region_after_render', [$name, $output]);

        return $output;
    },

    'region_field' => function($region, $fieldname, $key = null, $default = null) {


        $region = $this->get_region($region);

        if ($region && isset($region['fields']) && count($region['fields'])) {
            foreach ($region['fields'] as &$field) {

                if ($field['name'] == $fieldname) {

                    if ($key) {
                        return isset($field[$key]) ? $field[$key] : $default;
                    }

                    return $field;
                }
            }
        }

        return null;
    },

    'update_region_field' => function($region, $fieldname, $value) use($app) {

        $region = $this->get_region($region);

        if ($region && isset($region['fields']) && count($region['fields'])) {

            foreach ($region['fields'] as &$field) {

                if ($field['name'] == $fieldname) {

                    $field['value']     = $value;
                    $region["modified"] = time();

                    return $app->db->save("common/regions", $region);
                }
            }
        }

        return false;
    },

    'group' => function($group, $sort = null) use($app) {

        if (!$sort) $sort = ['name' => 1];

        return $app->db->find('common/regions', ['filter' =>['group' => $group], 'sort'=> $sort]);
    }
]);

// extend lexy parser
$app->renderer->extend(function($content){

    $content = preg_replace('/(\s*)@region\((.+?)\)/', '$1<?php echo cockpit("regions")->render($2); ?>', $content);

    return $content;
});

if (!function_exists('region')) {
    function region($name, $params = [], $locale = null) {
        echo cockpit('regions')->render($name, $params, $locale);
    }
}

if (!function_exists('get_region')) {
    function get_region($name, $params = [], $locale = null) {
        return cockpit('regions')->render($name, $params, $locale);
    }
}

if (!function_exists('regions_in_group')) {
    function regions_in_group($group, $sort = null) {
        return cockpit('regions')->group($group, $sort);
    }
}

if (!function_exists('region_field')) {
    function region_field($region, $field, $key = null, $default = null) {
        return cockpit('regions')->region_field($region, $field, $key, $default);
    }
}

// REST
$app->on('cockpit.rest.init', function($routes) {
    $routes["regions"] = 'Regions\\Controller\\RestApi';
});

// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_REST) include_once(__DIR__.'/admin.php');
