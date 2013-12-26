<?php


// make sure that $_SERVER['DOCUMENT_ROOT'] exists and is set correctly


$docsroot = $_SERVER['DOCUMENT_ROOT'];


return [

    "app.name"         => "Cockpit",
    "session.name"     => "cockpitsession",
    "sec-key"          => "c3b40c4c-db44-s5h7-a814-b4931a15e5e1",
    "base_url"         => "/".ltrim(str_replace($docsroot, '', str_replace(DIRECTORY_SEPARATOR, '/', __DIR__)), "/"),
    "base_route"    => "/".ltrim(str_replace($docsroot, '', str_replace(DIRECTORY_SEPARATOR, '/', __DIR__)).'/index.php', "/"), // if rewrite urls is disabled,
    //"base_route"       => "/".ltrim(str_replace($docsroot, '', str_replace(DIRECTORY_SEPARATOR, '/', __DIR__)), "/"),

    "docs_root"        => $docsroot

];