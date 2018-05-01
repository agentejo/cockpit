<?php

if (!COCKPIT_CLI) return;

$options = new ArrayObject([
    'src' => $app->param('--path', null)
]);


$app->trigger('cockpit.import', [$options]);
