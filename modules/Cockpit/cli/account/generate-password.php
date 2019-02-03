<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!COCKPIT_CLI) return;

$password = $app->param('passwd', null);

if (!$password) {
    return CLI::writeln("--passwd parameter is missing", false);
}

$password = $app->hash($password);

CLI::writeln($password, true);
