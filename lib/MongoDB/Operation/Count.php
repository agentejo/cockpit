<?php

namespace MongoDB\Operation;

use MongoDB\Driver\Command;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\Server;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Exception\UnexpectedValueException;

/**
 * Operation for the count command.
 *
 * @api
 * @see MongoDB\Collection::count()
 * @see http://docs.mongodb.org/manual/reference/command/count/
 */
class Count implements Executable
{
    private static $wireVersionForReadConcern = 4;

    private $databaseName;
    private $collectionName;
    private $filter;
    private $options;

    /**
     * Constructs a count command.
     *
     * Supported options:
     *
     *  * hint (string|document): The index to use. If a document, it will be
     *    interpretted as an index specification and a name will be generated.
     *
     *  * limit (integer): The maximum number of documents to count.
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
     *  * skip (integer): The number of documents to skip before returning the
     *    documents.
     *
     * @param string       $databaseName   Database name
     * @param string       $collectionName Collection name
     * @param array|object $filter         Query by which to filter documents
     * @param array        $options        Command options
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, $filter = [], array $options = [])
    {
        if ( ! is_array($filter) && ! is_object($filter)) {
            throw InvalidArgumentException::invalidType('$filter', $filter, 'array or object');
        }

        if (isset($options['hint'])) {
            if (is_array($options['hint']) || is_object($options['hint'])) {
                $options['hint'] = \MongoDB\generate_index_name($options['hint']);
            }

            if ( ! is_string($options['hint'])) {
                throw InvalidArgumentException::invalidType('"hint" option', $options['hint'], 'string or array or object');
            }
        }

        if (isset($options['limit']) && ! is_integer($options['limit'])) {
            throw InvalidArgumentException::invalidType('"limit" option', $options['limit'], 'integer');
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

        if (isset($options['skip']) && ! is_integer($options['skip'])) {
            throw InvalidArgumentException::invalidType('"skip" option', $options['skip'], 'integer');
        }

        $this->databaseName = (string) $databaseName;
        $this->collectionName = (string) $collectionName;
        $this->filter = $filter;
        $this->options = $options;
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     * @param Server $server
     * @return integer
     * @throws UnexpectedValueException if the command response was malformed
     */
    public function execute(Server $server)
    {
        $readPreference = isset($this->options['readPreference']) ? $this->options['readPreference'] : null;

        $cursor = $server->executeCommand($this->databaseName, $this->createCommand($server), $readPreference);
        $result = current($cursor->toArray());

        // Older server versions may return a float
        if ( ! isset($result->n) || ! (is_integer($result->n) || is_float($result->n))) {
            throw new UnexpectedValueException('count command did not return a numeric "n" value');
        }

        return (integer) $result->n;
    }

    /**
     * Create the count command.
     *
     * @param Server $server
     * @return Command
     */
    private function createCommand(Server $server)
    {
        $cmd = ['count' => $this->collectionName];

        if ( ! empty($this->filter)) {
            $cmd['query'] = (object) $this->filter;
        }

        foreach (['hint', 'limit', 'maxTimeMS', 'skip'] as $option) {
            if (isset($this->options[$option])) {
                $cmd[$option] = $this->options[$option];
            }
        }

        if (isset($this->options['readConcern']) && \MongoDB\server_supports_feature($server, self::$wireVersionForReadConcern)) {
            $cmd['readConcern'] = \MongoDB\read_concern_as_document($this->options['readConcern']);
        }

        return new Command($cmd);
    }
}
