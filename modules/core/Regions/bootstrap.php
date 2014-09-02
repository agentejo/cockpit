<?php

// API

$regions = []; #cached regions

$this->module("regions")->extend([

    'get_region' => function($name) use($app, $regions) {

        if (!isset($regions[$name])) {
            $regions[$name] = $app->db->findOne('common/regions', ['name'=>$name]);
        }

        return $regions[$name];
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
        $fields   = [];

        if (isset($region['fields']) && count($region['fields'])) {
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

//rest
$app->on('cockpit.rest.init', function($routes) {
    $routes["regions"] = 'Regions\\Controller\\RestApi';
});

// ADMIN

if (COCKPIT_ADMIN && !COCKPIT_REST) {


    $app->on("admin.init", function() {

        if (!$this->module("auth")->hasaccess("Regions", ['create.regions', 'edit.regions'])) return;

        $this->bindClass("Regions\\Controller\\Regions", "regions");
        $this->bindClass("Regions\\Controller\\Api", "api/regions");

        $this("admin")->menu("top", [
            "url"    => $this->routeUrl("/regions"),
            "label"  => '<i class="uk-icon-th-large"></i>',
            "title"  => $this("i18n")->get("Regions"),
            "active" => (strpos($this["route"], '/regions') === 0)
        ], 5);

        // handle global search request
        $this->on("cockpit.globalsearch", function($search, $list) {

            foreach ($this->db->find("common/regions") as $r) {
                if (stripos($r["name"], $search)!==false){
                    $list[] = [
                        "title" => '<i class="uk-icon-th-large"></i> '.$r["name"],
                        "url"   => $this->routeUrl('/regions/region/'.$r["_id"])
                    ];
                }
            }
        });
    });

    $app->on("admin.dashboard.aside", function() {

        if (!$this->module("auth")->hasaccess("Regions", ['create.regions', 'edit.regions'])) return;

        $title   = $this("i18n")->get("Regions");
        $badge   = $this->db->getCollection("common/regions")->count();
        $regions = $this->db->find("common/regions", ["limit"=> 3, "sort"=>["created"=>-1] ])->toArray();

        $this->renderView("regions:views/dashboard.php with cockpit:views/layouts/dashboard.widget.php", compact('title', 'badge', 'regions'));
    });


    // acl
    $app("acl")->addResource("Regions", ['create.regions', 'edit.regions', 'manage.region.fields']);

}
