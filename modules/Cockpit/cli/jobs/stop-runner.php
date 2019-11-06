<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

foreach ($app->storage->getKey('cockpit', 'jobs_queue_runners') as $pid) {
    
    if (posix_getsid($pid) !== false && !posix_kill($pid, /* SIGTERM */ 15)) {
        CLI::writeln("Failed to kill process: {$pid} (".posix_strerror(posix_get_last_error()).')', false);
    }
}

$app->storage->setKey('cockpit', 'jobs_queue_runners', []);