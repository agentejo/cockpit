<?php

// make sure that $_SERVER['DOCUMENT_ROOT'] exists and is set correctly
$DOCS_ROOT   = str_replace(DIRECTORY_SEPARATOR, '/', isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : dirname(__DIR__));
$COCKPIT_DIR = str_replace(DIRECTORY_SEPARATOR, '/', __DIR__);

$BASE        = trim(str_replace($DOCS_ROOT, '', $COCKPIT_DIR), "/");
$BASE_URL    = strlen($BASE) ? "/{$BASE}": $BASE;
$BASE_ROUTE  = "{$BASE_URL}/index.php";

$SERVER_NAME = isset($_SERVER["SERVER_NAME"])   ? $_SERVER["SERVER_NAME"] : 'localhost';

return [

    "app.name"          => "Cockpit",
    "session.name"      => "cockpitsession",
    "sec-key"           => "c3b40c4c-db44-s5h7-a814-b4931a15e5e1",
    "base_url"          => $BASE_URL,
    "base_route"        => $BASE_ROUTE,

    "i18n"              => "en",
    "docs_root"         => $DOCS_ROOT,

    "database"          => [ "server" => "mongolite://".("{$COCKPIT_DIR}/storage/data"), "options" => ["db" => "cockpitdb"] ],

    /* use mongodb as db storage
    "database"          => [ "server" => "mongodb://localhost:27017", "options" => ["db" => "cockpitdb"] ],
    */

    "mailer"            => [
        "from"      => "info@{$SERVER_NAME}",
        "transport" => "mail"
    ],

    /* mailer smtp settings
    "mailer"            => [
        "from"      => "info@mydomain.tld",
        "transport" => "smtp",
        "host"      => "",
        "user"      => "",
        "password"  => "xxxxxx",
        "port"      => 25,
        "auth"      => true,
        "encryption"=> ""    // '', ssl' or 'tls'
    ]
    */
];