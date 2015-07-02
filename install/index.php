<?php

$sqlitesupport = false;

// check whether sqlite is supported
try {

    if (extension_loaded('pdo')) {
        $test = new PDO('sqlite::memory:');
        $sqlitesupport = true;
    }

} catch (Exception $e) { }

// misc checks
$checks = array(
    "Php version >= 5.4.0"                              => (version_compare(PHP_VERSION, '5.4.0') >= 0),
    "PDO extension loaded with Sqlite support"          => $sqlitesupport,
    'Data  folder is not writable: /storage/data'       => is_writable(__DIR__.'/../storage/data'),
    'Cache folder is not writable: /storage/cache'      => is_writable(__DIR__.'/../storage/cache'),
    'Temp folder is not writable: /storage/tmp'         => is_writable(__DIR__.'/../storage/tmp'),
    'Uploads folder is not writable: /storage/uploads'  => is_writable(__DIR__.'/../storage/uploads'),
);

foreach($checks as $info => $check) {

    if (!$check) {
        include(__DIR__."/fail.php");
        exit;
    }
}

require(__DIR__.'/../bootstrap.php');

$app = cockpit();

// check whether cockpit is already installed
try {

    if ($app->storage->getCollection("cockpit/accounts")->count()) {
        header('Location: '.$app->baseUrl('/'));
        exit;
    }

} catch(Exception $e) { }

$account = [
    "user"     => "admin",
    "name"     => "Admin",
    "email"    => "admin@yourdomain.de",
    "active"   => true,
    "group"    => "admin",
    "password" => $app->hash("admin"),
    "i18n"     => "en"
];

$app->storage->insert("cockpit/accounts", $account);

include(__DIR__."/success.php");
