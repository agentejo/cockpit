<?php

$webhooks = $app->storage->find("cockpit/webhooks");

foreach ($webhooks as &$webhook) {

    if (count($webhook['events'])) {

        foreach ($webhook['events'] as $evt) {

            $app->on($evt, function() use($evt) {

            }, -1000);
        }
    }
}
