<?php
if ($context->user) {
    $_entry = cockpit()->module('collections')->findOne($context->entry['type'], ['_id' => $context->entry['item_id']]);
    
    if (empty($_entry['_id'])) {
        return cockpit()->stop('{"error": "Item not exist"}', 404);
    }
    // check already like
    $last = cockpit()->module('collections')->findOne("favourite", ['item_id' => $context->entry['item_id'], '_by' => $context->user['_id']]);
    if (!empty($last['_id'])) {
        return cockpit()->stop('{"error": "Already favourite"}', 400);
    }
}