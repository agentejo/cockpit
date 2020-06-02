<?php

if (!COCKPIT_CLI) return;

$oldName = $app->param('old', null);
$newName = $app->param('new', null);

if (!$oldName) {
    return CLI::writeln("--old parameter is missing", false);
}


if (!$newName) {
    return CLI::writeln("--new parameter is missing", false);
}

// check if old collection does exist
$origPath = $app->path("#storage:collections/{$oldName}.collection.php");
if (!$origPath) {
    return CLI::writeln("collection ${oldName} does not exist", false);
}

// Update collection's structure
$newPath = preg_replace('/'.$oldName.'.collection/', "{$newName}.collection", $origPath);
$newContent = preg_replace("/${oldName}/",$newName, file_get_contents($origPath));
if (file_put_contents($newPath, $newContent)) {
    CLI::writeln("Updated content", true);
} else {
    CLI::writeln("Content not updated", true);
}

// Export data from original collection
$items = $items = $app->storage->find("collections/{$oldName}")->toArray();
if (count($items)) {

    CLI::writeln("Exporting collections/{$oldName} (".count($items).")");

    // Import them into the new collection
    foreach ($items as $item){
        $app->storage->insert("collections/{$newName}", $item);
    }

    CLI::writeln("Items exported to collections/{$newName}");
}

// Remove the old file
unlink($origPath);
