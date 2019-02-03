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

$user = $app->param('user', null);

    if (!$user) {
        return CLI::writeln('--user parameter is missing', false);
    }

$email = $app->param('email', null);

    if (!$email) {
        return CLI::writeln('--email parameter is missing', false);
    }

    if (!$app->helper('utils')->isEmail($email)) {
        return CLI::writeln('Valid email required', false);
    }

$password = $app->param('passwd', null);

    if (!$password) {
        return CLI::writeln('--passwd (hashed password) parameter is missing', false);
    }

$created = time();

$account = [
    'user'     => $user,
    'name'     => $app->param('name', $user),
    'email'    => $email,
    'password' => $password,
    'active'   => true,
    'group'    => $app->param('group', 'admin'),
    'i18n'     => $app->param('i18n', 'en'),
    '_created' => $created,
    '_modified'=> $created,
];

// unique check
// --

$exist = $app->storage->findOne('cockpit/accounts', ['user'  => $user]);

    if ($exist) {
        return CLI::writeln('Username is already used!', false);
    }

$exist = $app->storage->findOne('cockpit/accounts', ['email'  => $email]);

    if ($exist) {
        return CLI::writeln('Email is already used!', false);
    }

// --

$app->storage->insert('cockpit/accounts', $account);

CLI::writeln('Account created', true);
