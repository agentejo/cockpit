<?php

namespace MongoDB\Operation;

use MongoDB\UpdateResult;
use MongoDB\Driver\Server;
use MongoDB\Exception\InvalidArgumentException;

/**
 * Operation for updating a single document with the update command.
 *
 * @api
 * @see MongoDB\Collection::updateOne()
 * @see http://docs.mongodb.org/manual/reference/command/update/
 */
class UpdateOne implements Executable
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
     * @param array|object $update         Update to apply to the matched document
     * @param array        $options        Command options
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, $filter, $update, array $options = [])
    {
        if ( ! is_array($update) && ! is_object($update)) {
            throw InvalidArgumentException::invalidType('$update', $update, 'array or object');
        }

        if ( ! \MongoDB\is_first_key_operator($update)) {
            throw new InvalidArgumentException('First key in $update argument is not an update operator');
        }

        $this->update = new Update(
            $databaseName,
            $collectionName,
            $filter,
            $update,
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
