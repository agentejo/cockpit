<?php

namespace MongoDB\Operation;

use MongoDB\Driver\Command;
use MongoDB\Driver\Server;
use MongoDB\Exception\InvalidArgumentException;

/**
 * Operation for the dropIndexes command.
 *
 * @api
 * @see MongoDB\Collection::dropIndexes()
 * @see http://docs.mongodb.org/manual/reference/command/dropIndexes/
 */
class DropIndexes implements Executable
{
    private $databaseName;
    private $collectionName;
    private $indexName;
    private $options;

    /**
     * Constructs a dropIndexes command.
     *
     * Supported options:
     *
     *  * typeMap (array): Type map for BSON deserialization. This will be used
     *    for the returned command result document.
     *
     * @param string $databaseName   Database name
     * @param string $collectionName Collection name
     * @param string $indexName      Index name (use "*" to drop all indexes)
     * @param array  $options        Command options
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, $indexName, array $options = [])
    {
        $indexName = (string) $indexName;

        if ($indexName === '') {
            throw new InvalidArgumentException('$indexName cannot be empty');
        }

        if (isset($options['typeMap']) && ! is_array($options['typeMap'])) {
            throw InvalidArgumentException::invalidType('"typeMap" option', $options['typeMap'], 'array');
        }

        $this->databaseName = (string) $databaseName;
        $this->collectionName = (string) $collectionName;
        $this->indexName = $indexName;
        $this->options = $options;
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     * @param Server $server
     * @return array|object Command result document
     */
    public function execute(Server $server)
    {
        $cmd = [
            'dropIndexes' => $this->collectionName,
            'index' => $this->indexName,
        ];

        $cursor = $server->executeCommand($this->databaseName, new Command($cmd));

        if (isset($this->options['typeMap'])) {
            $cursor->setTypeMap($this->options['typeMap']);
        }

        return current($cursor->toArray());
    }
}
