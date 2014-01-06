<?php


// make sure that $_SERVER['DOCUMENT_ROOT'] exists and is set correctly
$docsroot   = str_replace(DIRECTORY_SEPARATOR, '/', isset($_SERVER['DOCUMENT_ROOT']) ? (is_link($_SERVER['DOCUMENT_ROOT']) ? readlink($_SERVER['DOCUMENT_ROOT']) : $_SERVER['DOCUMENT_ROOT']) : dirname(__DIR__));
$servername = isset($_SERVER["SERVER_NAME"])   ? $_SERVER["SERVER_NAME"] : 'localhost';

return [

    "app.name"          => "Cockpit",
    "session.name"      => "cockpitsession",
    "sec-key"           => "c3b40c4c-db44-s5h7-a814-b4931a15e5e1",
    "base_url"          => "/".ltrim(str_replace($docsroot, '', str_replace(DIRECTORY_SEPARATOR, '/', __DIR__)), "/"),
    "base_route"        => "/".ltrim(str_replace($docsroot, '', str_replace(DIRECTORY_SEPARATOR, '/', __DIR__)).'/index.php', "/"),

    "i18n"              => "en",
    "docs_root"         => $docsroot,
    "addons_repository" => "https://raw.github.com/aheinze/cockpit-modules/master/modules.json",

    "mailer"            => [
        "from"      => "info@{$servername}",
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