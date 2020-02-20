<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!$app->helper('jobs')->isRunnerActive()) {
    return CLI::writeln("No active job queue runner found", false);
}

$app->helper('jobs')->stopRunner();

CLI::writeln("Cockpit: Job queue runner stopped", false);