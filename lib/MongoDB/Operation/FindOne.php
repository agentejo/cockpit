<?php

namespace MongoDB\Operation;

use MongoDB\Driver\Server;
use MongoDB\Exception\InvalidArgumentException;

/**
 * Operation for finding a single document with the find command.
 *
 * @api
 * @see MongoDB\Collection::findOne()
 * @see http://docs.mongodb.org/manual/tutorial/query-documents/
 * @see http://docs.mongodb.org/manual/reference/operator/query-modifier/
 */
class FindOne implements Executable
{
    private $find;
    private $options;

    /**
     * Constructs a find command for finding a single document.
     *
     * Supported options:
     *
     *  * comment (string): Attaches a comment to the query. If "$comment" also
     *    exists in the modifiers document, this option will take precedence.
     *
     *  * maxTimeMS (integer): The maximum amount of time to allow the query to
     *    run. If "$maxTimeMS" also exists in the modifiers document, this
     *    option will take precedence.
     *
     *  * modifiers (document): Meta-operators modifying the output or behavior
     *    of a query.
     *
     *  * projection (document): Limits the fields to return for the matching
     *    document.
     *
     *  * readConcern (MongoDB\Driver\ReadConcern): Read concern.
     *
     *    For servers < 3.2, this option is ignored as read concern is not
     *    available.
     *
     *  * readPreference (MongoDB\Driver\ReadPreference): Read preference.
     *
     *  * skip (integer): The number of documents to skip before returning.
     *
     *  * sort (document): The order in which to return matching documents. If
     *    "$orderby" also exists in the modifiers document, this option will
     *    take precedence.
     *
     *  * typeMap (array): Type map for BSON deserialization.
     *
     * @param string       $databaseName   Database name
     * @param string       $collectionName Collection name
     * @param array|object $filter         Query by which to filter documents
     * @param array        $options        Command options
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, $filter, array $options = [])
    {
        $this->find = new Find(
            $databaseName,
            $collectionName,
            $filter,
            ['limit' => 1] + $options
        );

        $this->options = $options;
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     * @param Server $server
     * @return array|object|null
     */
    public function execute(Server $server)
    {
        $cursor = $this->find->execute($server);
        $document = current($cursor->toArray());

        return ($document === false) ? null : $document;
    }
}
