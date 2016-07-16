<?php

namespace MongoDB\Operation;

use MongoDB\Driver\Command;
use MongoDB\Driver\Server;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Exception\UnexpectedValueException;
use MongoDB\Model\DatabaseInfoIterator;
use MongoDB\Model\DatabaseInfoLegacyIterator;

/**
 * Operation for the ListDatabases command.
 *
 * @api
 * @see MongoDB\Client::listDatabases()
 * @see http://docs.mongodb.org/manual/reference/command/ListDatabases/
 */
class ListDatabases implements Executable
{
    private $options;

    /**
     * Constructs a listDatabases command.
     *
     * Supported options:
     *
     *  * maxTimeMS (integer): The maximum amount of time to allow the query to
     *    run.
     *
     * @param array $options Command options
     * @throws InvalidArgumentException
     */
    public function __construct(array $options = [])
    {
        if (isset($options['maxTimeMS']) && ! is_integer($options['maxTimeMS'])) {
            throw InvalidArgumentException::invalidType('"maxTimeMS" option', $options['maxTimeMS'], 'integer');
        }

        $this->options = $options;
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     * @param Server $server
     * @return DatabaseInfoIterator
     * @throws UnexpectedValueException if the command response was malformed
     */
    public function execute(Server $server)
    {
        $cmd = ['listDatabases' => 1];

        if (isset($this->options['maxTimeMS'])) {
            $cmd['maxTimeMS'] = $this->options['maxTimeMS'];
        }

        $cursor = $server->executeCommand('admin', new Command($cmd));
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array']);
        $result = current($cursor->toArray());

        if ( ! isset($result['databases']) || ! is_array($result['databases'])) {
            throw new UnexpectedValueException('listDatabases command did not return a "databases" array');
        }

        /* Return an Iterator instead of an array in case listDatabases is
         * eventually changed to return a command cursor, like the collection
         * and index enumeration commands. This makes the "totalSize" command
         * field inaccessible, but users can manually invoke the command if they
         * need that value.
         */
        return new DatabaseInfoLegacyIterator($result['databases']);
    }
}
