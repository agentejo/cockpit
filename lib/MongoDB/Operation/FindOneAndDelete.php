<?php

namespace MongoDB\Operation;

use MongoDB\Driver\Command;
use MongoDB\Driver\Server;
use MongoDB\Exception\InvalidArgumentException;

/**
 * Operation for deleting a document with the findAndModify command.
 *
 * @api
 * @see MongoDB\Collection::findOneAndDelete()
 * @see http://docs.mongodb.org/manual/reference/command/findAndModify/
 */
class FindOneAndDelete implements Executable
{
    private $findAndModify;

    /**
     * Constructs a findAndModify command for deleting a document.
     *
     * Supported options:
     *
     *  * maxTimeMS (integer): The maximum amount of time to allow the query to
     *    run.
     *
     *  * projection (document): Limits the fields to return for the matching
     *    document.
     *
     *  * sort (document): Determines which document the operation modifies if
     *    the query selects multiple documents.
     *
     *  * writeConcern (MongoDB\Driver\WriteConcern): Write concern. This option
     *    is only supported for server versions >= 3.2.
     *
     * @param string       $databaseName   Database name
     * @param string       $collectionName Collection name
     * @param array|object $filter         Query by which to filter documents
     * @param array        $options        Command options
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, $filter, array $options = [])
    {
        if ( ! is_array($filter) && ! is_object($filter)) {
            throw InvalidArgumentException::invalidType('$filter', $filter, 'array or object');
        }

        if (isset($options['projection']) && ! is_array($options['projection']) && ! is_object($options['projection'])) {
            throw InvalidArgumentException::invalidType('"projection" option', $options['projection'], 'array or object');
        }

        if (isset($options['projection'])) {
            $options['fields'] = $options['projection'];
        }

        unset($options['projection']);

        $this->findAndModify = new FindAndModify(
            $databaseName,
            $collectionName,
            ['query' => $filter, 'remove' => true] + $options
        );
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     * @param Server $server
     * @return object|null
     */
    public function execute(Server $server)
    {
        return $this->findAndModify->execute($server);
    }
}
