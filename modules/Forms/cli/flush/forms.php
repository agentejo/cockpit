<?php

if (!COCKPIT_CLI) return;

CLI::writeln('Flushing forms data');


foreach ($app->module('forms')->forms() as $name => $form) {

    $fid = $form['_id'];
    $app->storage->dropCollection("forms/{$fid}");
}
