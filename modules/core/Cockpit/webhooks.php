<?php

// make sure curl extension is loaded
if (!function_exists('curl_init')) {
    return;
}

$webhooks = $app->storage->find("cockpit/webhooks");

foreach ($webhooks as &$webhook) {

    if ($webhook['active'] && $webhook['url'] && count($webhook['events'])) {

        foreach ($webhook['events'] as $evt) {

            $app->on($evt, function() use($evt, $webhook) {

                $ch   = curl_init($webhook['url']);
                $data = json_encode([
                    'event' => $evt,
                    'args' => func_get_args()
                ]);

                $header = [
                    'Content-Type: application/json',
                    'Content-Length: '.strlen($data)
                ];

                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

                curl_exec($ch);

            }, -1000);
        }
    }
}
