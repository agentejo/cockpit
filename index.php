<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Autoload vendor libs
include(__DIR__ . '/lib/vendor/autoload.php');

$configuration = require('app/config.php');
$appPath = __DIR__;
$publicPath = __DIR__;
$app = new \Cockpit\App($appPath, $publicPath, $configuration, \Cockpit\App::MODE_HTTP);
$app->boot();
$app->run();
