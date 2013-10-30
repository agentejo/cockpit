<?php


$sqlitesupport = false;

try {
    if(extension_loaded('pdo')) {
        $test = new PDO('sqlite::memory:');
        $sqlitesupport = true;
    }
} catch (Exception $e) { }

$checks = array(
    "Php version >= 5.4.0"                       => (version_compare(PHP_VERSION, '5.4.0') >= 0),
    "PDO extension loaded with Sqlite support" => $sqlitesupport,
    'Data  folder is writable: /data'          => is_writable(__DIR__.'/../storage/data'),
    'Cache folder is writable: /cache'         => is_writable(__DIR__.'/../storage/cache'),
    'Cache folder is writable: /cache/assets'  => is_writable(__DIR__.'/../storage/cache/assets'),
    'Cache folder is writable: /cache/thumbs'  => is_writable(__DIR__.'/../storage/cache/thumbs'),
);

foreach($checks as $info => $check) {
    if(!$check) {
        include(__DIR__."/fail.php");
        return;
    }
}

require(__DIR__.'/../bootstrap.php');

$app = c();

if($app->data->cockpit->accounts->count()) {
    header('Location: ../index.php');
    exit;
}

$account = [
    "user"     => "admin",
    "email"    => "test@test.de",
    "active"   => 1,
    "password" => $app->hash("admin")
];

$app->data->cockpit->accounts->insert($account);


include(__DIR__."/success.php");