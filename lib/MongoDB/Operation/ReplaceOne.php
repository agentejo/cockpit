<?php

namespace MongoDB\Operation;

use MongoDB\UpdateResult;
use MongoDB\Driver\Server;
use MongoDB\Exception\InvalidArgumentException;

/**
 * Operation for replacing a single document with the update command.
 *
 * @api
 * @see MongoDB\Collection::replaceOne()
 * @see http://docs.mongodb.org/manual/reference/command/update/
 */
class ReplaceOne implements Executable
{
    private $update;

    /**
     * Constructs an update command.
     *
     * Supported options:
     *
     *  * bypassDocumentValidation (boolean): If true, allows the write to opt
     *    out of document level validation.
     *
     *  * upsert (boolean): When true, a new document is created if no document
     *    matches the query. The default is false.
     *
     *  * writeConcern (MongoDB\Driver\WriteConcern): Write concern.
     *
     * @param string       $databaseName   Database name
     * @param string       $collectionName Collection name
     * @param array|object $filter         Query by which to filter documents
     * @param array|object $replacement    Replacement document
     * @param array        $options        Command options
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, $filter, $replacement, array $options = [])
    {
        if ( ! is_array($replacement) && ! is_object($replacement)) {
            throw InvalidArgumentException::invalidType('$replacement', $replacement, 'array or object');
        }

        if (\MongoDB\is_first_key_operator($replacement)) {
            throw new InvalidArgumentException('First key in $replacement argument is an update operator');
        }

        $this->update = new Update(
            $databaseName,
            $collectionName,
            $filter,
            $replacement,
            ['multi' => false] + $options
        );
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
        return $this->update->execute($server);
    }
}
