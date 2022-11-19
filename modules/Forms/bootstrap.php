<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$this->module('forms')->extend([

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
            '_id'       => $name,
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

        if (function_exists('opcache_reset')) opcache_reset();

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

            $form = $form['_id'];

            $this->app->helper('fs')->delete("#storage:forms/{$name}.form.php");
            $this->app->storage->dropCollection("forms/{$form}");

            return true;
        }

        return false;
    },

    'forms' => function($extended = false) {

        $stores = [];

        foreach($this->app->helper('fs')->ls('*.form.php', '#storage:forms') as $path) {

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

        $form = $forms['_id'];

        return $this->app->storage->getCollection("forms/{$form}");
    },

    'find' => function($form, $options = []) {

        $forms = $this->form($form);

        if (!$forms) return false;

        $form = $forms['_id'];

        // sort by custom order if form is sortable
        if (isset($forms['sortable']) && $forms['sortable'] && !isset($options['sort'])) {
            $options['sort'] = ['_order' => 1];
        }

        return (array)$this->app->storage->find("forms/{$form}", $options);
    },

    'findOne' => function($form, $criteria = [], $projection = null) {

        $forms = $this->form($form);

        if (!$forms) return false;

        $form = $forms['_id'];

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

        $this->app->trigger("forms.open.before", [$name, &$options]);

        $options = array_merge([
            'id'          => uniqid('form'),
            'class'       => '',
            'action'      => $this->app->routeUrl('/api/forms/submit/'.$name),
            'method'      => 'post',
            'enctype'     => 'multipart/form-data',
            'csrf'        => $this->app->hash($name),
            'mailsubject' => false,
            'include_js'  => true
        ], $options);

        $this->app->renderView('forms:views/api/form.php', compact('name', 'options'));

        $this->app->trigger("forms.open.after", [$name, &$options]);

    },

    'close' => function($name = null, $options = []) {

        $this->app->trigger("forms.close.before", [$name, &$options]);
        echo '</form>';
        $this->app->trigger("forms.close.after", [$name, &$options]);

    },

    'submit' => function($form, $data, $options = []) {

        $frm = $this->form($form);

        // Invalid form name
        if (!$frm) {
            return false;
        }

        // Load custom form validator
        if ($this->app->path("#config:forms/{$form}.php") && false===include($this->app->path("#config:forms/{$form}.php"))) {
            return false;
        }

        // Filter submitted data
        $this->app->trigger('forms.submit.before', [$form, &$data, $frm, &$options]);

        // Send email
        if (isset($frm['email_forward']) && $frm['email_forward']) {

            $emails          = array_map('trim', explode(',', $frm['email_forward']));
            $filtered_emails = [];

            // Validate each email address individually, push if valid
            foreach ($emails as $to) {
                if ($this->app->helper('utils')->isEmail($to)){
                    $filtered_emails[] = $to;
                }
            }

            if (count($filtered_emails)) {

                $frm['email_forward'] = implode(',', $filtered_emails);

                // Load custom email template
                if ($template = $this->app->path("#config:forms/emails/{$form}.php")) {
                    $body = $this->app->view($template, ['data' => $data, 'frm' => $frm]);
                }

                // Filter email content
                $this->app->trigger('forms.submit.email', [$form, &$data, $frm, &$body, &$options]);

                // Fallback to default email template
                if (empty($body)) {
                    $body = $this->app->view("forms:views/api/email.php", ['data' => $data, 'frm' => $frm]);
                }

                $formname = isset($frm['label']) && trim($frm['label']) ? $frm['label'] : $form;
                $to       = $frm['email_forward'];
                $subject  = $options['subject'] ?? $this->app->helper('i18n')->getstr("New form data for: %s", [$formname]);

                // success = true
                try {
                    $response = $this->app->mailer->mail($to, $subject, $body, $options);
                } catch (\Exception $e) {
                    $response = $e->getMessage();
                }
            }
        }

        // Push entry to database
        if (isset($frm['save_entry']) && $frm['save_entry']) {
            $entry = ['data' => $data];
            $this->save($form, $entry);
        }

        // Generate response array
        $response = (isset($response) && $response !== true) ? ['error' => $response, 'data' => $data] : $data;

        // Filter submission response
        $this->app->trigger('forms.submit.after', [$form, &$data, $frm, &$response]);

        return $response;
    }
]);

// ACL
$app('acl')->addResource('forms', ['create', 'delete', 'manage']);


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
if (COCKPIT_ADMIN_CP) {
    include_once(__DIR__.'/admin.php');
}

// CLI
if (COCKPIT_CLI) {
    $this->path('#cli', __DIR__.'/cli');
}
