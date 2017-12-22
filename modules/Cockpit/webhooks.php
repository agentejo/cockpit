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

                $url = trim($webhook['url']);
                $data = json_encode([
                    'event' => $evt,
                    'hook'  => $webhook['name'],
                    'backend' => COCKPIT_ADMIN,
                    'args' => func_get_args()
                ]);

                $headers = [
                    'Content-Type: application/json',
                    'Content-Length: '.strlen($data)
                ];

                // add custom headers
                if (isset($webhook['headers']) && is_array($webhook['headers']) && count($webhook['headers'])) {

                    foreach ($webhook['headers'] as &$h) {

                        if (!isset($h['k'], $h['v']) || !$h['k'] || !$h['v']) {
                            continue;
                        }
                        $headers[] = implode(': ', [$h['k'], $h['v']]);
                    }
                }

                $ch = curl_init($url);

                // add basic http auth
                if (isset($webhook['auth']) && $webhook['auth']['user'] && $webhook['auth']['pass']) {
                    curl_setopt($ch, CURLOPT_USERPWD, $webhook['auth']['user'] . ":" . $webhook['auth']['pass']);
                }

                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT, 1);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                curl_exec($ch);
                curl_close($ch);

            }, -1000);
        }
    }
}
