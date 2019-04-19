<?php

if (!COCKPIT_CLI) return;

$url  = $app->param('url', null);
$name = $app->param('name', null);

if (!$name) {
    return CLI::writeln('No addon name defined', false);;
}

$name = str_replace(['.', '/', ' '], '', $name);

if (!$url) {
    $url = "https://github.com/agentejo/{$name}/archive/master.zip";
}

$fs      = $app->helper('fs');
$tmppath = $app->path('#tmp:').'/'.$name;
$error   = false;
$zipname = null;

if (!is_writable($app->path('#addons:'))) {
    $error = 'Addons folder is not writable!';
}

CLI::writeln("Installing addon <{$name}>...🤖");

//download
if (!$fs->mkdir($tmppath)) {
    return CLI::writeln("Couldn't create tmp folder {$tmppath}", false);
}

$zipname = basename($url);

if (!$fs->write("{$tmppath}/{$zipname}", $handle = @fopen($url, 'r'))) {
    $error = "Couldn't download {$url}!";
}
@fclose($handle);

if ($error) {
    return CLI::writeln($error, false);
}

$fs->mkdir("{$tmppath}/extract-{$zipname}");
$zip = new \ZipArchive;

if ($zip->open("{$tmppath}/{$zipname}") === true) {

    if (!$zip->extractTo("{$tmppath}/extract-{$zipname}")) {
        $error = 'Extracting zip file failed!';
    }
    $zip->close();
} else {
    $error = 'Open zip file failed!';
}

if ($error) {
    return CLI::writeln($error, false);
}

$addonRoot = null;

// find addon root
foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator("{$tmppath}/extract-{$zipname}")) as $file) {
    
    if ($file->getFilename() == 'bootstrap.php') {
        $addonRoot = dirname($file->getRealPath());
        break;
    }
}

if (!$addonRoot) {
    return CLI::writeln("No addon found!", false);
}

if (!$fs->mkdir("#addons:{$name}")) {
    return CLI::writeln("Couldn't create addons folder {$name}", false);
}

$fs->copy($addonRoot, "#addons:{$name}");

CLI::writeln("Addon <{$name}> installed! ✅", true);

// cleanup
$fs->delete($tmppath);



