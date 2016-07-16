<?php

namespace MongoDB\Operation;

use MongoDB\Driver\Command;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\Server;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Exception\UnexpectedValueException;

/**
 * Operation for the distinct command.
 *
 * @api
 * @see MongoDB\Collection::distinct()
 * @see http://docs.mongodb.org/manual/reference/command/distinct/
 */
class Distinct implements Executable
{
    private static $wireVersionForReadConcern = 4;

    private $databaseName;
    private $collectionName;
    private $fieldName;
    private $filter;
    private $options;

    /**
     * Constructs a distinct command.
     *
     * Supported options:
     *
     *  * maxTimeMS (integer): The maximum amount of time to allow the query to
     *    run.
     *
     *  * readConcern (MongoDB\Driver\ReadConcern): Read concern.
     *
     *    For servers < 3.2, this option is ignored as read concern is not
     *    available.
     *
     *  * readPreference (MongoDB\Driver\ReadPreference): Read preference.
     *
     * @param string       $databaseName   Database name
     * @param string       $collectionName Collection name
     * @param string       $fieldName      Field for which to return distinct values
     * @param array|object $filter         Query by which to filter documents
     * @param array        $options        Command options
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, $fieldName, $filter = [], array $options = [])
    {
        if ( ! is_array($filter) && ! is_object($filter)) {
            throw InvalidArgumentException::invalidType('$filter', $filter, 'array or object');
        }

        if (isset($options['maxTimeMS']) && ! is_integer($options['maxTimeMS'])) {
            throw InvalidArgumentException::invalidType('"maxTimeMS" option', $options['maxTimeMS'], 'integer');
        }

        if (isset($options['readConcern']) && ! $options['readConcern'] instanceof ReadConcern) {
            throw InvalidArgumentException::invalidType('"readConcern" option', $options['readConcern'], 'MongoDB\Driver\ReadConcern');
        }

        if (isset($options['readPreference']) && ! $options['readPreference'] instanceof ReadPreference) {
            throw InvalidArgumentException::invalidType('"readPreference" option', $options['readPreference'], 'MongoDB\Driver\ReadPreference');
        }

        $this->databaseName = (string) $databaseName;
        $this->collectionName = (string) $collectionName;
        $this->fieldName = (string) $fieldName;
        $this->filter = $filter;
        $this->options = $options;
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     * @param Server $server
     * @return mixed[]
     * @throws UnexpectedValueException if the command response was malformed
     */
    public function execute(Server $server)
    {
        $readPreference = isset($this->options['readPreference']) ? $this->options['readPreference'] : null;

        $cursor = $server->executeCommand($this->databaseName, $this->createCommand($server), $readPreference);
        $result = current($cursor->toArray());

        if ( ! isset($result->values) || ! is_array($result->values)) {
            throw new UnexpectedValueException('distinct command did not return a "values" array');
        }

        return $result->values;
    }

    /**
     * Create the distinct command.
     *
     * @param Server $server
     * @return Command
     */
    private function createCommand(Server $server)
    {
        $cmd = [
            'distinct' => $this->collectionName,
            'key' => $this->fieldName,
        ];

        if ( ! empty($this->filter)) {
            $cmd['query'] = (object) $this->filter;
        }

        if (isset($this->options['maxTimeMS'])) {
            $cmd['maxTimeMS'] = $this->options['maxTimeMS'];
        }

        if (isset($this->options['readConcern']) && \MongoDB\server_supports_feature($server, self::$wireVersionForReadConcern)) {
            $cmd['readConcern'] = \MongoDB\read_concern_as_document($this->options['readConcern']);
        }

        return new Command($cmd);
    }
}
