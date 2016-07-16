<?php

namespace MongoDB\Operation;

use MongoDB\BulkWriteResult;
use MongoDB\Driver\BulkWrite as Bulk;
use MongoDB\Driver\Server;
use MongoDB\Driver\WriteConcern;
use MongoDB\Exception\InvalidArgumentException;

/**
 * Operation for executing multiple write operations.
 *
 * @api
 * @see MongoDB\Collection::bulkWrite()
 */
class BulkWrite implements Executable
{
    const DELETE_MANY = 'deleteMany';
    const DELETE_ONE  = 'deleteOne';
    const INSERT_ONE  = 'insertOne';
    const REPLACE_ONE = 'replaceOne';
    const UPDATE_MANY = 'updateMany';
    const UPDATE_ONE  = 'updateOne';

    private static $wireVersionForDocumentLevelValidation = 4;

    private $databaseName;
    private $collectionName;
    private $operations;
    private $options;

    /**
     * Constructs a bulk write operation.
     *
     * Example array structure for all supported operation types:
     *
     *  [
     *    [ 'deleteMany' => [ $filter ] ],
     *    [ 'deleteOne'  => [ $filter ] ],
     *    [ 'insertOne'  => [ $document ] ],
     *    [ 'replaceOne' => [ $filter, $replacement, $options ] ],
     *    [ 'updateMany' => [ $filter, $update, $options ] ],
     *    [ 'updateOne'  => [ $filter, $update, $options ] ],
     *  ]
     *
     * Arguments correspond to the respective Operation classes; however, the
     * writeConcern option is specified for the top-level bulk write operation
     * instead of each individual operation.
     *
     * Supported options for replaceOne, updateMany, and updateOne operations:
     *
     *  * upsert (boolean): When true, a new document is created if no document
     *    matches the query. The default is false.
     *
     * Supported options for the bulk write operation:
     *
     *  * bypassDocumentValidation (boolean): If true, allows the write to opt
     *    out of document level validation.
     *
     *  * ordered (boolean): If true, when an insert fails, return without
     *    performing the remaining writes. If false, when a write fails,
     *    continue with the remaining writes, if any. The default is true.
     *
     *  * writeConcern (MongoDB\Driver\WriteConcern): Write concern.
     *
     * @param string  $databaseName   Database name
     * @param string  $collectionName Collection name
     * @param array[] $operations     List of write operations
     * @param array   $options        Command options
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, array $operations, array $options = [])
    {
        if (empty($operations)) {
            throw new InvalidArgumentException('$operations is empty');
        }

        $expectedIndex = 0;

        foreach ($operations as $i => $operation) {
            if ($i !== $expectedIndex) {
                throw new InvalidArgumentException(sprintf('$operations is not a list (unexpected index: "%s")', $i));
            }

            if ( ! is_array($operation)) {
                throw InvalidArgumentException::invalidType(sprintf('$operations[%d]', $i), $operation, 'array');
            }

            if (count($operation) !== 1) {
                throw new InvalidArgumentException(sprintf('Expected one element in $operation[%d], actually: %d', $i, count($operation)));
            }

            $type = key($operation);
            $args = current($operation);

            if ( ! isset($args[0]) && ! array_key_exists(0, $args)) {
                throw new InvalidArgumentException(sprintf('Missing first argument for $operations[%d]["%s"]', $i, $type));
            }

            if ( ! is_array($args[0]) && ! is_object($args[0])) {
                throw InvalidArgumentException::invalidType(sprintf('$operations[%d]["%s"][0]', $i, $type), $args[0], 'array or object');
            }

            switch ($type) {
                case self::INSERT_ONE:
                    break;

                case self::DELETE_MANY:
                case self::DELETE_ONE:
                    $operations[$i][$type][1] = ['limit' => ($type === self::DELETE_ONE ? 1 : 0)];

                    break;

                case self::REPLACE_ONE:
                    if ( ! isset($args[1]) && ! array_key_exists(1, $args)) {
                        throw new InvalidArgumentException(sprintf('Missing second argument for $operations[%d]["%s"]', $i, $type));
                    }

                    if ( ! is_array($args[1]) && ! is_object($args[1])) {
                        throw InvalidArgumentException::invalidType(sprintf('$operations[%d]["%s"][1]', $i, $type), $args[1], 'array or object');
                    }

                    if (\MongoDB\is_first_key_operator($args[1])) {
                        throw new InvalidArgumentException(sprintf('First key in $operations[%d]["%s"][1] is an update operator', $i, $type));
                    }

                    if ( ! isset($args[2])) {
                        $args[2] = [];
                    }

                    if ( ! is_array($args[2])) {
                        throw InvalidArgumentException::invalidType(sprintf('$operations[%d]["%s"][2]', $i, $type), $args[2], 'array');
                    }

                    $args[2]['multi'] = false;
                    $args[2] += ['upsert' => false];

                    if ( ! is_bool($args[2]['upsert'])) {
                        throw InvalidArgumentException::invalidType(sprintf('$operations[%d]["%s"][2]["upsert"]', $i, $type), $args[2]['upsert'], 'boolean');
                    }

                    $operations[$i][$type][2] = $args[2];

                    break;

                case self::UPDATE_MANY:
                case self::UPDATE_ONE:
                    if ( ! isset($args[1]) && ! array_key_exists(1, $args)) {
                        throw new InvalidArgumentException(sprintf('Missing second argument for $operations[%d]["%s"]', $i, $type));
                    }

                    if ( ! is_array($args[1]) && ! is_object($args[1])) {
                        throw InvalidArgumentException::invalidType(sprintf('$operations[%d]["%s"][1]', $i, $type), $args[1], 'array or object');
                    }

                    if ( ! \MongoDB\is_first_key_operator($args[1])) {
                        throw new InvalidArgumentException(sprintf('First key in $operations[%d]["%s"][1] is not an update operator', $i, $type));
                    }

                    if ( ! isset($args[2])) {
                        $args[2] = [];
                    }

                    if ( ! is_array($args[2])) {
                        throw InvalidArgumentException::invalidType(sprintf('$operations[%d]["%s"][2]', $i, $type), $args[2], 'array');
                    }

                    $args[2]['multi'] = ($type === self::UPDATE_MANY);
                    $args[2] += ['upsert' => false];

                    if ( ! is_bool($args[2]['upsert'])) {
                        throw InvalidArgumentException::invalidType(sprintf('$operations[%d]["%s"][2]["upsert"]', $i, $type), $args[2]['upsert'], 'boolean');
                    }

                    $operations[$i][$type][2] = $args[2];

                    break;

                default:
                    throw new InvalidArgumentException(sprintf('Unknown operation type "%s" in $operations[%d]', $type, $i));
            }

            $expectedIndex += 1;
        }

        $options += ['ordered' => true];

        if (isset($options['bypassDocumentValidation']) && ! is_bool($options['bypassDocumentValidation'])) {
            throw InvalidArgumentException::invalidType('"bypassDocumentValidation" option', $options['bypassDocumentValidation'], 'boolean');
        }

        if ( ! is_bool($options['ordered'])) {
            throw InvalidArgumentException::invalidType('"ordered" option', $options['ordered'], 'boolean');
        }

        if (isset($options['writeConcern']) && ! $options['writeConcern'] instanceof WriteConcern) {
            throw InvalidArgumentException::invalidType('"writeConcern" option', $options['writeConcern'], 'MongoDB\Driver\WriteConcern');
        }

        $this->databaseName = (string) $databaseName;
        $this->collectionName = (string) $collectionName;
        $this->operations = $operations;
        $this->options = $options;
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     * @param Server $server
     * @return BulkWriteResult
     */
    public function execute(Server $server)
    {
        $options = ['ordered' => $this->options['ordered']];

        if (isset($this->options['bypassDocumentValidation']) && \MongoDB\server_supports_feature($server, self::$wireVersionForDocumentLevelValidation)) {
            $options['bypassDocumentValidation'] = $this->options['bypassDocumentValidation'];
        }

        $bulk = new Bulk($options);
        $insertedIds = [];

        foreach ($this->operations as $i => $operation) {
            $type = key($operation);
            $args = current($operation);

            switch ($type) {
                case self::DELETE_MANY:
                case self::DELETE_ONE:
                    $bulk->delete($args[0], $args[1]);
                    break;

                case self::INSERT_ONE:
                    $insertedId = $bulk->insert($args[0]);

                    if ($insertedId !== null) {
                        $insertedIds[$i] = $insertedId;
                    } else {
                        $insertedIds[$i] = \MongoDB\extract_id_from_inserted_document($args[0]);
                    }

                    break;

                case self::REPLACE_ONE:
                case self::UPDATE_MANY:
                case self::UPDATE_ONE:
                    $bulk->update($args[0], $args[1], $args[2]);
            }
        }

        $writeConcern = isset($this->options['writeConcern']) ? $this->options['writeConcern'] : null;
        $writeResult = $server->executeBulkWrite($this->databaseName . '.' . $this->collectionName, $bulk, $writeConcern);

        return new BulkWriteResult($writeResult, $insertedIds);
    }
}
