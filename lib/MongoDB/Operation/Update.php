<?php

namespace MongoDB\Operation;

use MongoDB\UpdateResult;
use MongoDB\Driver\BulkWrite as Bulk;
use MongoDB\Driver\Server;
use MongoDB\Driver\WriteConcern;
use MongoDB\Exception\InvalidArgumentException;

/**
 * Operation for the update command.
 *
 * This class is used internally by the ReplaceOne, UpdateMany, and UpdateOne
 * operation classes.
 *
 * @internal
 * @see http://docs.mongodb.org/manual/reference/command/update/
 */
class Update implements Executable
{
    private static $wireVersionForDocumentLevelValidation = 4;

    private $databaseName;
    private $collectionName;
    private $filter;
    private $update;
    private $options;

    /**
     * Constructs a update command.
     *
     * Supported options:
     *
     *  * bypassDocumentValidation (boolean): If true, allows the write to opt
     *    out of document level validation.
     *
     *  * multi (boolean): When true, updates all documents matching the query.
     *    This option cannot be true if the $update argument is a replacement
     *    document (i.e. contains no update operators). The default is false.
     *
     *  * upsert (boolean): When true, a new document is created if no document
     *    matches the query. The default is false.
     *
     *  * writeConcern (MongoDB\Driver\WriteConcern): Write concern.
     *
     * @param string       $databaseName   Database name
     * @param string       $collectionName Collection name
     * @param array|object $filter         Query by which to delete documents
     * @param array|object $update         Update to apply to the matched
     *                                     document(s) or a replacement document
     * @param array        $options        Command options
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, $filter, $update, array $options = [])
    {
        if ( ! is_array($filter) && ! is_object($filter)) {
            throw InvalidArgumentException::invalidType('$filter', $filter, 'array or object');
        }

        if ( ! is_array($update) && ! is_object($update)) {
            throw InvalidArgumentException::invalidType('$update', $filter, 'array or object');
        }

        $options += [
            'multi' => false,
            'upsert' => false,
        ];

        if (isset($options['bypassDocumentValidation']) && ! is_bool($options['bypassDocumentValidation'])) {
            throw InvalidArgumentException::invalidType('"bypassDocumentValidation" option', $options['bypassDocumentValidation'], 'boolean');
        }

        if ( ! is_bool($options['multi'])) {
            throw InvalidArgumentException::invalidType('"multi" option', $options['multi'], 'boolean');
        }

        if ($options['multi'] && ! \MongoDB\is_first_key_operator($update)) {
            throw new InvalidArgumentException('"multi" option cannot be true if $update is a replacement document');
        }

        if ( ! is_bool($options['upsert'])) {
            throw InvalidArgumentException::invalidType('"upsert" option', $options['upsert'], 'boolean');
        }

        if (isset($options['writeConcern']) && ! $options['writeConcern'] instanceof WriteConcern) {
            throw InvalidArgumentException::invalidType('"writeConcern" option', $options['writeConcern'], 'MongoDB\Driver\WriteConcern');
        }

        $this->databaseName = (string) $databaseName;
        $this->collectionName = (string) $collectionName;
        $this->filter = $filter;
        $this->update = $update;
        $this->options = $options;
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     * @param Server $server
     * @return UpdateResult
     */
    public function execute(Server $server)
    {
        $updateOptions = [
            'multi' => $this->options['multi'],
            'upsert' => $this->options['upsert'],
        ];

        $bulkOptions = [];

        if (isset($this->options['bypassDocumentValidation']) && \MongoDB\server_supports_feature($server, self::$wireVersionForDocumentLevelValidation)) {
            $bulkOptions['bypassDocumentValidation'] = $this->options['bypassDocumentValidation'];
        }

        $bulk = new Bulk($bulkOptions);
        $bulk->update($this->filter, $this->update, $updateOptions);

        $writeConcern = isset($this->options['writeConcern']) ? $this->options['writeConcern'] : null;
        $writeResult = $server->executeBulkWrite($this->databaseName . '.' . $this->collectionName, $bulk, $writeConcern);

        return new UpdateResult($writeResult);
    }
}
