<?php
if ($context->user) {
    $_entry = cockpit()->module('collections')->findOne($context->entry['type'], ['_id' => $context->entry['item_id']]);
    
    if (empty($_entry['_id'])) {
        return cockpit()->stop('{"error": "Item not exist"}', 404);
    }
    // TODO: check da like
}