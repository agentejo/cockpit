<?php


// make sure that $_SERVER['DOCUMENT_ROOT'] exists and is set correctly


$docsroot = $_SERVER['DOCUMENT_ROOT'];


return [

    "app.name"          => "Cockpit",
    "session.name"      => "cockpitsession",
    "sec-key"           => "c3b40c4c-db44-s5h7-a814-b4931a15e5e1",
    "base_url"          => "/".ltrim(str_replace($docsroot, '', str_replace(DIRECTORY_SEPARATOR, '/', __DIR__)), "/"),
    "base_route"        => "/".ltrim(str_replace($docsroot, '', str_replace(DIRECTORY_SEPARATOR, '/', __DIR__)).'/index.php', "/"),

    "docs_root"         => $docsroot,
    "addons_repository" => "https://raw.github.com/aheinze/cockpit-modules/master/modules.json",

    "mailer"            => [
        "from"      => "info@".$_SERVER["SERVER_NAME"],
        "transport" => "mail"
    ],

    /* mailer smtp settings
    "mailer"            => [
        "from"      => "info@".$_SERVER["SERVER_NAME"],
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