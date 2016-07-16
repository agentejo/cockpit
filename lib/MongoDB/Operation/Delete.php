<?php

namespace MongoDB\Operation;

use MongoDB\DeleteResult;
use MongoDB\Driver\BulkWrite as Bulk;
use MongoDB\Driver\Server;
use MongoDB\Driver\WriteConcern;
use MongoDB\Exception\InvalidArgumentException;

/**
 * Operation for the delete command.
 *
 * This class is used internally by the DeleteMany and DeleteOne operation
 * classes.
 *
 * @internal
 * @see http://docs.mongodb.org/manual/reference/command/delete/
 */
class Delete implements Executable
{
    private $databaseName;
    private $collectionName;
    private $filter;
    private $limit;
    private $options;

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
     * @param integer      $limit          The number of matching documents to
     *                                     delete. Must be 0 or 1, for all or a
     *                                     single document, respectively.
     * @param array        $options        Command options
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, $filter, $limit, array $options = [])
    {
        if ( ! is_array($filter) && ! is_object($filter)) {
            throw InvalidArgumentException::invalidType('$filter', $filter, 'array or object');
        }

        if ($limit !== 0 && $limit !== 1) {
            throw new InvalidArgumentException('$limit must be 0 or 1');
        }

        if (isset($options['writeConcern']) && ! $options['writeConcern'] instanceof WriteConcern) {
            throw InvalidArgumentException::invalidType('"writeConcern" option', $options['writeConcern'], 'MongoDB\Driver\WriteConcern');
        }

        $this->databaseName = (string) $databaseName;
        $this->collectionName = (string) $collectionName;
        $this->filter = $filter;
        $this->limit = $limit;
        $this->options = $options;
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
        $bulk = new Bulk();
        $bulk->delete($this->filter, ['limit' => $this->limit]);

        $writeConcern = isset($this->options['writeConcern']) ? $this->options['writeConcern'] : null;
        $writeResult = $server->executeBulkWrite($this->databaseName . '.' . $this->collectionName, $bulk, $writeConcern);

        return new DeleteResult($writeResult);
    }
}
