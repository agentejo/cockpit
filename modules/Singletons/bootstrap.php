<?php

$this->module("singletons")->extend([

    'createSingleton' => function($name, $data = []) {

        if (!trim($name)) {
            return false;
        }

        $configpath = $this->app->path('#storage:').'/singleton';

        if (!$this->app->path('#storage:singleton')) {

            if (!$this->app->helper('fs')->mkdir($configpath)) {
                return false;
            }
        }

        if ($this->exists($name)) {
            return false;
        }

        $time = time();

        $singleton = array_replace_recursive([
            'name'      => $name,
            'label'     => $name,
            '_id'       => uniqid($name),
            'fields'    => [],
            'template'  => '',
            'data'      => null,
            '_created'  => $time,
            '_modified' => $time
        ], $data);

        $this->app->trigger("singleton.save.before", [$singleton]);
        $this->app->trigger("singleton.save.before.{$name}", [$singleton]);

        $export = var_export($singleton, true);

        if (!$this->app->helper('fs')->write("#storage:singleton/{$name}.singleton.php", "<?php\n return {$export};")) {
            return false;
        }

        $this->app->trigger("singleton.save.after", [$singleton]);
        $this->app->trigger("singleton.save.after.{$name}", [$singleton]);

        return $singleton;
    },

    'updateSingleton' => function($name, $data) {

        $metapath = $this->app->path("#storage:singleton/{$name}.singleton.php");

        if (!$metapath) {
            return false;
        }

        $data['_modified'] = time();

        $singleton  = include($metapath);
        $singleton  = array_merge($singleton, $data);


        $this->app->trigger("singleton.save.before", [$singleton]);
        $this->app->trigger("singleton.save.before.{$name}", [$singleton]);

        $export  = var_export($singleton, true);

        if (!$this->app->helper('fs')->write($metapath, "<?php\n return {$export};")) {
            return false;
        }

        $this->app->trigger('singleton.save.after', [$singleton]);
        $this->app->trigger("singleton.save.after.{$name}", [$singleton]);

        return $singleton;
    },

    'saveSingleton' => function($name, $data) {

        if (!trim($name)) {
            return false;
        }

        return isset($data['_id']) ? $this->updateSingleton($name, $data) : $this->createSingleton($name, $data);
    },

    'removeSingleton' => function($name) {

        if ($singleton = $this->singleton($name)) {

            $this->app->helper("fs")->delete("#storage:singleton/{$name}.singleton.php");

            $this->app->trigger('singleton.remove', [$singleton]);
            $this->app->trigger("singleton.remove.{$name}", [$singleton]);

            return true;
        }

        return false;
    },

    'saveData' => function($name, $data) {

        if ($singleton = $this->singleton($name)) {

            $this->app->trigger('singleton.saveData.before', [$singleton, &$data]);
            $this->app->trigger("singleton.saveData.before.{$name}", [$singleton, &$data]);

            $this->app->storage->setKey('singletons', $name, $data);

            $this->app->trigger('singleton.saveData.after', [$singleton, $data]);
            $this->app->trigger("singleton.saveData.after.{$name}", [$singleton, $data]);

            return true;
        }

        return false;
    },

    'getData' => function($name) {

        if ($singleton = $this->singleton($name)) {

            $value = $this->app->storage->getKey('singletons', $name);

            $this->app->trigger('singleton.getData.after', [$singleton, &$value]);
            $this->app->trigger("singleton.getData.after.{$name}", [$singleton, &$value]);

            return $value;
        }

        return null;
    },

    'singletons' => function() {

        $singletons = [];

        foreach ($this->app->helper("fs")->ls('*.singleton.php', '#storage:singleton') as $path) {

            $store = include($path->getPathName());
            $singletons[$store['name']] = $store;
        }

        return $singletons;
    },

    'exists' => function($name) {
        return $this->app->path("#storage:singleton/{$name}.singleton.php");
    },

    'singleton' => function($name) {

        static $singleton; // cache

        if (is_null($singleton)) {
            $singleton = [];
        }

        if (!is_string($name)) {
            return false;
        }

        if (!isset($singleton[$name])) {

            $singleton[$name] = false;

            if ($path = $this->exists($name)) {
                $singleton[$name] = include($path);
            }
        }

        return $singleton[$name];
    },

    'getSingletonFieldValue' => function($singleton, $fieldname, $default = null) {

        $data = $this->getData($singleton);

        return ($data && isset($data[$fieldname])) ? $data[$fieldname] : $default;
    }

]);

// ACL
$app("acl")->addResource("singletons", ['create', 'delete']);

$this->module("singletons")->extend([

    'getSingletonsInGroup' => function($group = null) {

        if (!$group) {
            $group = $this->app->module('cockpit')->getGroup();
        }

        $_singletons = $this->singletons();
        $singletons = [];

        if ($this->app->module('cockpit')->isSuperAdmin()) {
            return $_singletons;
        }

        foreach ($_singletons as $singleton => $meta) {

            if (isset($meta['acl'][$group]['form']) && $meta['acl'][$group]['form']) {
                $singletons[$singleton] = $meta;
            }
        }

        return $singletons;
    },

    'hasaccess' => function($singleton, $action, $group = null) {

        $singleton = $this->singleton($singleton);

        if (!$singleton) {
            return false;
        }

        if (!$group) {
            $group = $this->app->module('cockpit')->getGroup();
        }

        if ($this->app->module('cockpit')->isSuperAdmin($group)) {
            return true;
        }

        if (isset($singleton['acl'][$group][$action])) {
            return $singleton['acl'][$group][$action];
        }

        return false;
    }
]);


// extend app lexy parser
$app->renderer->extend(function($content){
    $content = preg_replace('/(\s*)@singleton\((.+?)\)/', '$1<?php echo cockpit("singleton")->render($2); ?>', $content);
    return $content;
});

// REST
if (COCKPIT_API_REQUEST) {

    $app->on('cockpit.rest.init', function($routes) {
        $routes['singleton'] = 'Singleton\\Controller\\RestApi';
    });

    // allow access to public collections
    $app->on('cockpit.api.authenticate', function($data) {

        if ($data['user'] || $data['resource'] != 'singleton') return;

        if (isset($data['query']['params'][1])) {

            $singleton = $this->module('singleton')->singleton($data['query']['params'][1]);

            if ($singleton && isset($singleton['acl']['public'])) {
                $data['authenticated'] = true;
                $data['user'] = ['_id' => null, 'group' => 'public'];
            }
        }
    });
}

// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {
    include_once(__DIR__.'/admin.php');
}
