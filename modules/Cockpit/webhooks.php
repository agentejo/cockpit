<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// make sure curl extension is loaded
if (!function_exists('curl_init')) {
    return;
}

// optimize webhooks loading
if ($cachepath = $this->path('#tmp:webhooks.cache.php')) {
    $webhooks = include($cachepath);
} else {
    $webhooks = $app->storage->find('cockpit/webhooks')->toArray();
    $this->helper('fs')->write('#tmp:webhooks.cache.php', '<?php return '.var_export($webhooks, true ).';');
}

if (!count($webhooks)) {
    return;
}

$webHookCalls = new ArrayObject([]);

foreach ($webhooks as $webhook) {

    if ($webhook['active'] && $webhook['url'] && count($webhook['events'])) {

        foreach ($webhook['events'] as $evt) {

            $app->on($evt, function() use($evt, $webhook, $webHookCalls) {

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

                $auth = $webhook['auth'] ?? null; 

                $webHookCalls[] = compact('url', 'data', 'headers', 'auth');

            }, -1000);
        }
    }
}

$app->on('shutdown', function() use($webHookCalls) {
        
    if (!count($webHookCalls)) {
        return;
    }

    foreach ($webHookCalls as $webhook) {

        $this->trigger('cockpit.webhook', [&$webhook]);

        $ch = curl_init($webhook['url']);

        // add basic http auth
        if (isset($webhook['auth']) && $webhook['auth']['user'] && $webhook['auth']['pass']) {
            curl_setopt($ch, CURLOPT_USERPWD, $webhook['auth']['user'] . ":" . $webhook['auth']['pass']);
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $webhook['data']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $webhook['headers']);

        curl_exec($ch);
        curl_close($ch);
    }
});