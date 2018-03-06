<?php


$this->module("forms")->extend([

    'createForm' => function($name, $data = []) {

        if (!trim($name)) {
            return false;
        }

        $configpath = $this->app->path('#storage:').'/forms';

        if (!$this->app->path('#storage:forms')) {

            if (!$this->app->helper('fs')->mkdir($configpath)) {
                return false;
            }
        }

        if ($this->exists($name)) {
            return false;
        }

        $time = time();

        $form = array_replace_recursive([
            '_id'       => uniqid($name),
            'name'      => $name,
            'label'     => $name,
            'save_entry' => true,
            'in_menu'   => false,
            'email_forward' => '',
            '_created'  => $time,
            '_modified' => $time
        ], $data);

        $export = var_export($form, true);

        if (!$this->app->helper('fs')->write("#storage:forms/{$name}.form.php", "<?php\n return {$export};")) {
            return false;
        }

        return $form;
    },

    'updateForm' => function($name, $data) {

        $metapath = $this->app->path("#storage:forms/{$name}.form.php");

        if (!$metapath) {
            return false;
        }

        $data['_modified'] = time();

        $form   = include($metapath);
        $form   = array_merge($form, $data);
        $export = var_export($form, true);

        if (!$this->app->helper('fs')->write($metapath, "<?php\n return {$export};")) {
            return false;
        }

        return $form;
    },

    'saveForm' => function($name, $data) {

        if (!trim($name)) {
            return false;
        }

        return isset($data['_id']) ? $this->updateForm($name, $data) : $this->createForm($name, $data);
    },

    'removeForm' => function($name) {

        if ($form = $this->form($name)) {

            $form = $forms["_id"];

            $this->app->helper("fs")->delete("#storage:forms/{$name}.form.php");
            $this->app->storage->dropform("forms/{$form}");

            return true;
        }

        return false;
    },

    'forms' => function($extended = false) {

        $stores = [];

        foreach($this->app->helper("fs")->ls('*.form.php', '#storage:forms') as $path) {

            $store = include($path->getPathName());

            if ($extended) {
                $store['itemsCount'] = $this->count($store['name']);
            }

            $stores[$store['name']] = $store;
        }

        return $stores;
    },

    'exists' => function($name) {
        return $this->app->path("#storage:forms/{$name}.form.php");
    },

    'form' => function($name) {

        static $forms; // cache

        if (is_null($forms)) {
            $forms = [];
        }

        if (!is_string($name)) {
            return false;
        }

        if (!isset($forms[$name])) {

            $forms[$name] = false;

            if ($path = $this->exists($name)) {
                $forms[$name] = include($path);
            }
        }

        return $forms[$name];
    },

    'entries' => function($name) {

        $forms = $this->form($name);

        if (!$forms) return false;

        $form = $forms["_id"];

        return $this->app->storage->getform("forms/{$form}");
    },

    'find' => function($form, $options = []) {

        $forms = $this->form($form);

        if (!$forms) return false;

        $form = $forms["_id"];

        // sort by custom order if form is sortable
        if (isset($forms['sortable']) && $forms['sortable'] && !isset($options['sort'])) {
            $options['sort'] = ['_order' => 1];
        }

        return (array)$this->app->storage->find("forms/{$form}", $options);
    },

    'findOne' => function($form, $criteria = [], $projection = null) {

        $forms = $this->form($form);

        if (!$forms) return false;

        $form = $forms["_id"];

        return $this->app->storage->findOne("forms/{$form}", $criteria, $projection);
    },

    'save' => function($form, $data) {

        $forms = $this->form($form);

        if (!$forms) return false;

        $name       = $form;
        $form       = $forms['_id'];
        $data       = isset($data[0]) ? $data : [$data];
        $return     = [];
        $modified   = time();

        foreach ($data as $entry) {

            $isUpdate = isset($entry['_id']);

            $entry['_modified'] = $modified;

            if (!$isUpdate) {
                $entry['_created'] = $entry['_modified'];
            }

            $this->app->trigger('forms.save.before', [$name, &$entry]);
            $this->app->trigger("forms.save.before.{$name}", [$name, &$entry]);

            $ret = $this->app->storage->save("forms/{$form}", $entry);

            $this->app->trigger('forms.save.after', [$name, &$entry]);
            $this->app->trigger("forms.save.after.{$name}", [$name, &$entry]);

            $return[] = $ret ? $entry : false;
        }

        return count($return) == 1 ? $return[0] : $return;
    },

    'remove' => function($form, $criteria) {

        $forms = $this->form($form);

        if (!$forms) return false;

        $form = $forms['_id'];

        return $this->app->storage->remove("forms/{$form}", $criteria);
    },

    'count' => function($form, $criteria = []) {

        $forms = $this->form($form);

        if (!$forms) return false;

        $form = $forms['_id'];

        return $this->app->storage->count("forms/{$form}", $criteria);
    },

    'open' => function($name, $options = []) {

        $options = array_merge(array(
            "id"    => uniqid('form'),
            "class" => "",
            "csrf"  => $this->app->hash($name)
        ), $options);

        $this->app->renderView("forms:views/api/form.php", compact('name', 'options'));
    },

    'submit' => function($form, $data, $options = []) {

        $frm = $this->form($form);

        if (!$frm) {
            return false;
        }

        // custom form validation
        if ($this->app->path("#config:forms/{$form}.php") && false===include($this->app->path("#config:forms/{$form}.php"))) {
            return false;
        }

        if (isset($frm['email_forward']) && $frm['email_forward']) {

            $emails          = array_map('trim', explode(',', $frm['email_forward']));
            $filtered_emails = [];

            foreach ($emails as $to){

                // Validate each email address individually, push if valid
                if (filter_var($to, FILTER_VALIDATE_EMAIL)){
                    $filtered_emails[] = $to;
                }
            }

            if (count($filtered_emails)) {

                $frm['email_forward'] = implode(',', $filtered_emails);

                // There is an email template available
                if ($template = $this->app->path("#config:forms/emails/{$form}.php")) {

                    $body = $this->app->renderer->file($template, $data, false);

                // Prepare template manually
                } else {

                    $body = [];

                    foreach ($data as $key => $value) {
                        $body[] = "<b>{$key}:</b>\n<br>";
                        $body[] = (is_string($value) ? $value:json_encode($value))."\n<br>";
                    }

                    $body = implode("\n<br>", $body);
                }

                $formname = isset($frm['label']) && trim($frm['label']) ? $frm['label'] : $form;

                $this->app->mailer->mail($frm['email_forward'], $this->param("__mailsubject", "New form data for: {$formname}"), $body, $options);
            }
        }

        if (isset($frm['save_entry']) && $frm['save_entry']) {
            $entry = ['data' => $data];
            $this->save($form, $entry);
        }

        return $data;
    }
]);

// ACL
$app('acl')->addResource('forms', ['manage']);


// REST
if (COCKPIT_API_REQUEST) {

    $this->bind('/api/forms/submit/:form', function($params) {

        $form = $params["form"];
        $formhash = $this->param('__csrf', false);

        // Security check
        if (!password_verify($form, $formhash)) {
            return false;
        }

        if ($data = $this->param('form', false)) {
            return $this->module('forms')->submit($form, $data, $this->param('form_options', []));
        }

        return false;

    }, $this->param('__csrf', false));

    if (!$this->param('__csrf', false)) {

        $app->on('cockpit.rest.init', function($routes) {
            $routes['forms'] = 'Forms\\Controller\\RestApi';
        });
    }
}

// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {

    include_once(__DIR__.'/admin.php');
}
