<?php

$sqlitesupport = false;

try {
    if(extension_loaded('pdo')) {
        $test = new PDO('sqlite::memory:');
        $sqlitesupport = true;
    }
} catch (Exception $e) { }

$checks = array(
    "Php version >= 5.4.0"                             => (version_compare(PHP_VERSION, '5.4.0') >= 0),
    "PDO extension loaded with Sqlite support"         => $sqlitesupport,
    'Data  folder is writable: /storage/data'          => is_writable(__DIR__.'/../storage/data'),
    'Cache folder is writable: /storage/cache'         => is_writable(__DIR__.'/../storage/cache'),
    'Cache folder is writable: /storage/cache/assets'  => is_writable(__DIR__.'/../storage/cache/assets'),
    'Cache folder is writable: /storage/cache/thumbs'  => is_writable(__DIR__.'/../storage/cache/thumbs'),
);

foreach($checks as $info => $check) {
    if(!$check) {
        include(__DIR__."/fail.php");
        return;
    }
}

require(__DIR__.'/../bootstrap.php');

$app = cockpit();

if($app->db->getCollection("cockpit/accounts")->count()) {
    header('Location: ../index.php');
    exit;
}

$account = [
    "user"     => "admin",
    "name"     => "",
    "email"    => "test@test.de",
    "active"   => 1,
    "group"    => "admin",
    "password" => $app->hash("admin"),
    "i18n"     => "en"
];

$app->db->insert("cockpit/accounts", $account);

include(__DIR__."/success.php");
