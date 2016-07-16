<?php

namespace MongoDB\Operation;

use MongoDB\Driver\Command;
use MongoDB\Driver\Query;
use MongoDB\Driver\Server;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Model\CollectionInfoCommandIterator;
use MongoDB\Model\CollectionInfoIterator;
use MongoDB\Model\CollectionInfoLegacyIterator;

/**
 * Operation for the listCollections command.
 *
 * @api
 * @see MongoDB\Database::listCollections()
 * @see http://docs.mongodb.org/manual/reference/command/listCollections/
 */
class ListCollections implements Executable
{
    private static $wireVersionForCommand = 3;

    private $databaseName;
    private $options;

    /**
     * Constructs a listCollections command.
     *
     * Supported options:
     *
     *  * filter (document): Query by which to filter collections.
     *
     *  * maxTimeMS (integer): The maximum amount of time to allow the query to
     *    run.
     *
     * @param string $databaseName Database name
     * @param array  $options      Command options
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, array $options = [])
    {
        if (isset($options['filter']) && ! is_array($options['filter']) && ! is_object($options['filter'])) {
            throw InvalidArgumentException::invalidType('"filter" option', $options['filter'], 'array or object');
        }

        if (isset($options['maxTimeMS']) && ! is_integer($options['maxTimeMS'])) {
            throw InvalidArgumentException::invalidType('"maxTimeMS" option', $options['maxTimeMS'], 'integer');
        }

        $this->databaseName = (string) $databaseName;
        $this->options = $options;
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     * @param Server $server
     * @return CollectionInfoIterator
     */
    public function execute(Server $server)
    {
        return \MongoDB\server_supports_feature($server, self::$wireVersionForCommand)
            ? $this->executeCommand($server)
            : $this->executeLegacy($server);
    }

    /**
     * Returns information for all collections in this database using the
     * listCollections command.
     *
     * @param Server $server
     * @return CollectionInfoCommandIterator
     */
    private function executeCommand(Server $server)
    {
        $cmd = ['listCollections' => 1];

        if ( ! empty($this->options['filter'])) {
            $cmd['filter'] = (object) $this->options['filter'];
        }

        if (isset($this->options['maxTimeMS'])) {
            $cmd['maxTimeMS'] = $this->options['maxTimeMS'];
        }

        $cursor = $server->executeCommand($this->databaseName, new Command($cmd));
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array']);

        return new CollectionInfoCommandIterator($cursor);
    }

    /**
     * Returns information for all collections in this database by querying the
     * "system.namespaces" collection (MongoDB <3.0).
     *
     * @param Server $server
     * @return CollectionInfoLegacyIterator
     * @throws InvalidArgumentException if filter.name is not a string.
     */
    private function executeLegacy(Server $server)
    {
        $filter = empty($this->options['filter']) ? [] : (array) $this->options['filter'];

        if (array_key_exists('name', $filter)) {
            if ( ! is_string($filter['name'])) {
                throw InvalidArgumentException::invalidType('filter name for MongoDB <3.0', $filter['name'], 'string');
            }

            $filter['name'] = $this->databaseName . '.' . $filter['name'];
        }

        $options = isset($this->options['maxTimeMS'])
            ? ['modifiers' => ['$maxTimeMS' => $this->options['maxTimeMS']]]
            : [];

        $cursor = $server->executeQuery($this->databaseName . '.system.namespaces', new Query($filter, $options));
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array']);

        return new CollectionInfoLegacyIterator($cursor);
    }
}
