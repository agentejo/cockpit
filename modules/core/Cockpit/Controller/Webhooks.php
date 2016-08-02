<?php

namespace Cockpit\Controller;

class Webhooks extends \Cockpit\AuthController {

    public function index() {

        $webhooks = $this->storage->find("cockpit/webhooks", [
            "sort"   => ["name" => 1]
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

            $webhook = $this->storage->findOne("cockpit/webhooks", ['_id' => $id]);

            if (!$webhook) {
                return false;
            }
        }

        return $this->render('cockpit:views/webhooks/webhook.php', compact('webhook'));
    }

    public function save() {

        if ($data = $this->param("webhook", false)) {

            $data['_modified'] = time();

            if (!isset($data["_id"])) {
                $data['_created'] = $data['_modified'];
            }

            $this->app->storage->save("cockpit/webhooks", $data);

            return json_encode($data);
        }

        return false;

    }

    public function remove() {

        if ($data = $this->param("webhook", false)) {

            $this->app->storage->remove("cockpit/webhooks", ['_id'=>$data['_id']]);

            return true;
        }

        return false;

    }
}
