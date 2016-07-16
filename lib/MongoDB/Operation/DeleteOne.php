<?php

namespace MongoDB\Operation;

use MongoDB\DeleteResult;
use MongoDB\Driver\Server;
use MongoDB\Exception\InvalidArgumentException;

/**
 * Operation for deleting a single document with the delete command.
 *
 * @api
 * @see MongoDB\Collection::deleteOne()
 * @see http://docs.mongodb.org/manual/reference/command/delete/
 */
class DeleteOne implements Executable
{
    private $delete;

    /**
     * Constructs a delete command.
     *
     * Supported options:
     *
     *  * writeConcern (MongoDB\Driver\WriteConcern): Write concern.
     *
     * @param string       $databaseName   Database name
     * @param string       $collectionName Collection name
     * @param array|object $filter         Query by which to delete documents
     * @param array        $options        Command options
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, $filter, array $options = [])
    {
        $this->delete = new Delete($databaseName, $collectionName, $filter, 1, $options);
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     * @param Server $server
     * @return DeleteResult
     */
    public function execute(Server $server)
    {
        return $this->delete->execute($server);
    }
}
