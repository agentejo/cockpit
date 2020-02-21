<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$runnerIdle = intval($app->param('idle', 2));

if (!$runnerIdle) {
    return CLI::writeln('--idle parameter is not valid', false);
}

if ($app->param('f')) {
    $app->helper('jobs')->stopRunner();
}

if ($app->helper('jobs')->isRunnerActive()) {
    return CLI::writeln("A job queue runner is already active", false);
}

CLI::writeln('Cockpit: Job queue runner started', true);

$app->on('shutdown', function() {
    CLI::writeln('Job queue runner stopped', false);  
});

$app->helper('jobs')->run($runnerIdle);