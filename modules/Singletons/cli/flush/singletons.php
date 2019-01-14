<?php

if (!COCKPIT_CLI) return;

CLI::writeln('Flushing singletons data');


foreach ($app->module('singletons')->singletons() as $name => $singleton) {

    $app->storage->removeKey('singletons', $name);
}
