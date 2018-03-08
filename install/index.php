<?php

define('COCKPIT_INSTALL', true);

$sqlitesupport = false;

// check whether sqlite is supported
try {

    if (extension_loaded('pdo')) {
        $test = new PDO('sqlite::memory:');
        $sqlitesupport = true;
    }

} catch (Exception $e) { }

require(__DIR__.'/../bootstrap.php');

// misc checks
$checks = array(
    "Php version >= 5.4.0"                              => (version_compare(PHP_VERSION, '5.4.0') >= 0),
    "PDO extension loaded with Sqlite support"          => $sqlitesupport,
    'Data  folder is not writable: /storage/data'       => is_writable(COCKPIT_STORAGE_FOLDER.'/data'),
    'Cache folder is not writable: /storage/cache'      => is_writable(COCKPIT_STORAGE_FOLDER.'/cache'),
    'Temp folder is not writable: /storage/tmp'         => is_writable(COCKPIT_STORAGE_FOLDER.'/tmp'),
    'Uploads folder is not writable: /storage/uploads'  => is_writable(COCKPIT_STORAGE_FOLDER.'/uploads'),
);

foreach($checks as $info => $check) {

    if (!$check) {
        include(__DIR__."/fail.php");
        exit;
    }
}

$app = cockpit();

// check whether cockpit is already installed
try {

    if ($app->storage->getCollection("cockpit/accounts")->count()) {
        header('Location: '.$app->baseUrl('/'));
        exit;
    }

} catch(Exception $e) { }

$created = time();

$account = [
    "user"     => "admin",
    "name"     => "Admin",
    "email"    => "admin@yourdomain.de",
    "active"   => true,
    "group"    => "admin",
    "password" => $app->hash("admin"),
    "i18n"     => "en",
    "_created" => $created,
    "_modified"=> $created,
];

$app->storage->insert("cockpit/accounts", $account);

include(__DIR__."/success.php");
