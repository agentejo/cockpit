<?php

namespace MongoDB;

use MongoDB\BSON\Serializable;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\Server;
use MongoDB\Exception\InvalidArgumentException;
use stdClass;

/**
 * Extracts an ID from an inserted document.
 *
 * This function is used when BulkWrite::insert() does not return a generated
 * ID, which means that the ID should be fetched from an array offset, public
 * property, or in the data returned by bsonSerialize().
 *
 * @internal
 * @see https://jira.mongodb.org/browse/PHPC-382
 * @param array|object $document Inserted document
 * @return mixed
 */
function extract_id_from_inserted_document($document)
{
    if ($document instanceof Serializable) {
        return extract_id_from_inserted_document($document->bsonSerialize());
    }

    return is_array($document) ? $document['_id'] : $document->_id;
}

/**
 * Generate an index name from a key specification.
 *
 * @internal
 * @param array|object $document Document containing fields mapped to values,
 *                               which denote order or an index type
 * @return string
 * @throws InvalidArgumentException
 */
function generate_index_name($document)
{
    if (is_object($document)) {
        $document = get_object_vars($document);
    }

    if ( ! is_array($document)) {
        throw InvalidArgumentException::invalidType('$document', $document, 'array or object');
    }

    $name = '';

    foreach ($document as $field => $type) {
        $name .= ($name != '' ? '_' : '') . $field . '_' . $type;
    }

    return $name;
}

/**
 * Return whether the first key in the document starts with a "$" character.
 *
 * This is used for differentiating update and replacement documents.
 *
 * @internal
 * @param array|object $document Update or replacement document
 * @return boolean
 * @throws InvalidArgumentException
 */
function is_first_key_operator($document)
{
    if (is_object($document)) {
        $document = get_object_vars($document);
    }

    if ( ! is_array($document)) {
        throw InvalidArgumentException::invalidType('$document', $document, 'array or object');
    }

    reset($document);
    $firstKey = (string) key($document);

    return (isset($firstKey[0]) && $firstKey[0] === '$');
}

/**
 * Return whether the aggregation pipeline ends with an $out operator.
 *
 * This is used for determining whether the aggregation pipeline msut be
 * executed against a primary server.
 *
 * @internal
 * @param array $pipeline List of pipeline operations
 * @return boolean
 */
function is_last_pipeline_operator_out(array $pipeline)
{
    $lastOp = end($pipeline);

    if ($lastOp === false) {
        return false;
    }

    $lastOp = (array) $lastOp;

    return key($lastOp) === '$out';
}

/**
 * Converts a ReadConcern instance to a stdClass for use in a BSON document.
 *
 * @internal
 * @see https://jira.mongodb.org/browse/PHPC-498
 * @param ReadConcern $readConcern Read concern
 * @return stdClass
 */
function read_concern_as_document(ReadConcern $readConcern)
{
    $document = [];

    if ($readConcern->getLevel() !== null) {
        $document['level'] = $readConcern->getLevel();
    }

    return (object) $document;
}

/**
 * Return whether the server supports a particular feature.
 *
 * @internal
 * @param Server  $server  Server to check
 * @param integer $feature Feature constant (i.e. wire protocol version)
 * @return boolean
 */
function server_supports_feature(Server $server, $feature)
{
    $info = $server->getInfo();
    $maxWireVersion = isset($info['maxWireVersion']) ? (integer) $info['maxWireVersion'] : 0;
    $minWireVersion = isset($info['minWireVersion']) ? (integer) $info['minWireVersion'] : 0;

    return ($minWireVersion <= $feature && $maxWireVersion >= $feature);
}

function is_string_array($input) {
    if (!is_array($input)){
        return false;
    }
    foreach($input as $item) {
        if (!is_string($item)) {
            return false;
        }
    }
    return true;
}

