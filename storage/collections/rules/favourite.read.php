<?php
if ($context->user && $context->user['group'] != 'admin') {
    $context->options['filter']['_by'] = $context->user['_id'];
}