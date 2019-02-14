<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cockpit\Controller;

class Webhooks extends \Cockpit\AuthController {

    public function __construct($app) {

        parent::__construct($app);

        if (!$this->module('cockpit')->hasaccess('cockpit', 'webhooks')) {
            return $this->helper('admin')->denyRequest();
        }
    }

    public function index() {

        $webhooks = $this->app->storage->find('cockpit/webhooks', [
            'sort' => ['name' => 1]
        ])->toArray();

        return $this->render('cockpit:views/webhooks/index.php', compact('webhooks'));
    }

    public function webhook($id = null) {

        $webhook = [
            'name' => '',
            'url'  => '',
            'auth' => ['user'=>'', 'pass'=>''],
            'headers' => [],
            'events' => [],
            'active' => true
        ];

        if ($id) {

            $webhook = $this->app->storage->findOne('cockpit/webhooks', ['_id' => $id]);

            if (!$webhook) {
                return false;
            }
        }

        $triggers = new \ArrayObject([
            'admin.init',
            'app.{$controller}.init',
            'cockpit.account.login',
            'cockpit.account.logout',
            'cockpit.api.authenticate',
            'cockpit.api.erroronrequest',
            'cockpit.assets.list',
            'cockpit.assets.remove',
            'cockpit.assets.save',
            'cockpit.bootstrap',
            'cockpit.clearcache',
            'cockpit.export',
            'cockpit.import',
            'cockpit.media.removefiles',
            'cockpit.media.rename',
            'cockpit.media.upload',
            'cockpit.request.error',
            'cockpit.rest.init',
            'cockpit.update.after',
            'cockpit.update.before',
            'shutdown',
        ]);

        $this->app->trigger('cockpit.webhook.events', [$triggers]);

        $triggers = $triggers->getArrayCopy();

        return $this->render('cockpit:views/webhooks/webhook.php', compact('webhook', 'triggers'));
    }

    public function save() {

        if ($data = $this->param('webhook', false)) {

            $data['_modified'] = time();

            if (!isset($data['_id'])) {
                $data['_created'] = $data['_modified'];
            }

            $this->app->storage->save('cockpit/webhooks', $data);

            // invalidate cache
            if ($cache = $this->app->path('#tmp:webhooks.cache.php')) {
                @unlink($cache);
            }

            return json_encode($data);
        }

        return false;

    }

    public function remove() {

        if ($data = $this->param('webhook', false)) {

            $this->app->storage->remove('cockpit/webhooks', ['_id'=>$data['_id']]);

            // invalidate cache
            if ($cache = $this->app->path('#tmp:webhooks.cache.php')) {
                @unlink($cache);
            }

            return true;
        }

        return false;

    }
}
