<?php

namespace MongoDB\Operation;

use MongoDB\Driver\Command;
use MongoDB\Driver\Server;
use MongoDB\Exception\InvalidArgumentException;

/**
 * Operation for the dropDatabase command.
 *
 * @api
 * @see MongoDB\Client::dropDatabase()
 * @see MongoDB\Database::drop()
 * @see http://docs.mongodb.org/manual/reference/command/dropDatabase/
 */
class DropDatabase implements Executable
{
    private $databaseName;
    private $options;

    /**
     * Constructs a dropDatabase command.
     *
     * Supported options:
     *
     *  * typeMap (array): Type map for BSON deserialization. This will be used
     *    for the returned command result document.
     *
     * @param string $databaseName Database name
     * @param array  $options      Command options
     */
    public function __construct($databaseName, array $options = [])
    {
        if (isset($options['typeMap']) && ! is_array($options['typeMap'])) {
            throw InvalidArgumentException::invalidType('"typeMap" option', $options['typeMap'], 'array');
        }

        $this->databaseName = (string) $databaseName;
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
        $cursor = $server->executeCommand($this->databaseName, new Command(['dropDatabase' => 1]));

        if (isset($this->options['typeMap'])) {
            $cursor->setTypeMap($this->options['typeMap']);
        }

        return current($cursor->toArray());
    }
}
