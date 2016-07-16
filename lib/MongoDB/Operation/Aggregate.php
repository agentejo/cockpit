<?php

namespace MongoDB\Operation;

use MongoDB\Driver\Command;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\Server;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Exception\UnexpectedValueException;
use ArrayIterator;
use stdClass;
use Traversable;

/**
 * Operation for the aggregate command.
 *
 * @api
 * @see MongoDB\Collection::aggregate()
 * @see http://docs.mongodb.org/manual/reference/command/aggregate/
 */
class Aggregate implements Executable
{
    private static $wireVersionForCursor = 2;
    private static $wireVersionForDocumentLevelValidation = 4;
    private static $wireVersionForReadConcern = 4;

    private $databaseName;
    private $collectionName;
    private $pipeline;
    private $options;

    /**
     * Constructs an aggregate command.
     *
     * Supported options:
     *
     *  * allowDiskUse (boolean): Enables writing to temporary files. When set
     *    to true, aggregation stages can write data to the _tmp sub-directory
     *    in the dbPath directory. The default is false.
     *
     *  * batchSize (integer): The number of documents to return per batch.
     *
     *  * bypassDocumentValidation (boolean): If true, allows the write to opt
     *    out of document level validation. This only applies when the $out
     *    stage is specified.
     *
     *    For servers < 3.2, this option is ignored as document level validation
     *    is not available.
     *
     *  * maxTimeMS (integer): The maximum amount of time to allow the query to
     *    run.
     *
     *  * readConcern (MongoDB\Driver\ReadConcern): Read concern. Note that a
     *    "majority" read concern is not compatible with the $out stage.
     *
     *    For servers < 3.2, this option is ignored as read concern is not
     *    available.
     *
     *  * readPreference (MongoDB\Driver\ReadPreference): Read preference.
     *
     *  * typeMap (array): Type map for BSON deserialization. This will be
     *    applied to the returned Cursor (it is not sent to the server).
     *
     *    This is not supported for inline aggregation results (i.e. useCursor
     *    option is false or the server versions < 2.6).
     *
     *  * useCursor (boolean): Indicates whether the command will request that
     *    the server provide results using a cursor. The default is true.
     *
     *    For servers < 2.6, this option is ignored as aggregation cursors are
     *    not available.
     *
     *    For servers >= 2.6, this option allows users to turn off cursors if
     *    necessary to aid in mongod/mongos upgrades.
     *
     * @param string $databaseName   Database name
     * @param string $collectionName Collection name
     * @param array  $pipeline       List of pipeline operations
     * @param array  $options        Command options
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, array $pipeline, array $options = [])
    {
        if (empty($pipeline)) {
            throw new InvalidArgumentException('$pipeline is empty');
        }

        $expectedIndex = 0;

        foreach ($pipeline as $i => $operation) {
            if ($i !== $expectedIndex) {
                throw new InvalidArgumentException(sprintf('$pipeline is not a list (unexpected index: "%s")', $i));
            }

            if ( ! is_array($operation) && ! is_object($operation)) {
                throw InvalidArgumentException::invalidType(sprintf('$pipeline[%d]', $i), $operation, 'array or object');
            }

            $expectedIndex += 1;
        }

        $options += [
            'allowDiskUse' => false,
            'useCursor' => true,
        ];

        if ( ! is_bool($options['allowDiskUse'])) {
            throw InvalidArgumentException::invalidType('"allowDiskUse" option', $options['allowDiskUse'], 'boolean');
        }

        if (isset($options['batchSize']) && ! is_integer($options['batchSize'])) {
            throw InvalidArgumentException::invalidType('"batchSize" option', $options['batchSize'], 'integer');
        }

        if (isset($options['bypassDocumentValidation']) && ! is_bool($options['bypassDocumentValidation'])) {
            throw InvalidArgumentException::invalidType('"bypassDocumentValidation" option', $options['bypassDocumentValidation'], 'boolean');
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

        if (isset($options['typeMap']) && ! is_array($options['typeMap'])) {
            throw InvalidArgumentException::invalidType('"typeMap" option', $options['typeMap'], 'array');
        }

        if ( ! is_bool($options['useCursor'])) {
            throw InvalidArgumentException::invalidType('"useCursor" option', $options['useCursor'], 'boolean');
        }

        if (isset($options['batchSize']) && ! $options['useCursor']) {
            throw new InvalidArgumentException('"batchSize" option should not be used if "useCursor" is false');
        }

        if (isset($options['typeMap']) && ! $options['useCursor']) {
            throw new InvalidArgumentException('"typeMap" option should not be used if "useCursor" is false');
        }

        $this->databaseName = (string) $databaseName;
        $this->collectionName = (string) $collectionName;
        $this->pipeline = $pipeline;
        $this->options = $options;
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     * @param Server $server
     * @return Traversable
     * @throws UnexpectedValueException if the command response was malformed
     */
    public function execute(Server $server)
    {
        $isCursorSupported = \MongoDB\server_supports_feature($server, self::$wireVersionForCursor);
        $readPreference = isset($this->options['readPreference']) ? $this->options['readPreference'] : null;

        $command = $this->createCommand($server, $isCursorSupported);
        $cursor = $server->executeCommand($this->databaseName, $command, $readPreference);

        if ($isCursorSupported && $this->options['useCursor']) {
            /* The type map can only be applied to command cursors until
             * https://jira.mongodb.org/browse/PHPC-314 is implemented.
             */
            if (isset($this->options['typeMap'])) {
                $cursor->setTypeMap($this->options['typeMap']);
            }

            return $cursor;
        }

        $result = current($cursor->toArray());

        if ( ! isset($result->result) || ! is_array($result->result)) {
            throw new UnexpectedValueException('aggregate command did not return a "result" array');
        }

        return new ArrayIterator($result->result);
    }

    /**
     * Create the aggregate command.
     *
     * @param Server  $server
     * @param boolean $isCursorSupported
     * @return Command
     */
    private function createCommand(Server $server, $isCursorSupported)
    {
        $cmd = [
            'aggregate' => $this->collectionName,
            'pipeline' => $this->pipeline,
        ];

        // Servers < 2.6 do not support any command options
        if ( ! $isCursorSupported) {
            return new Command($cmd);
        }

        $cmd['allowDiskUse'] = $this->options['allowDiskUse'];

        if (isset($this->options['bypassDocumentValidation']) && \MongoDB\server_supports_feature($server, self::$wireVersionForDocumentLevelValidation)) {
            $cmd['bypassDocumentValidation'] = $this->options['bypassDocumentValidation'];
        }

        if (isset($this->options['maxTimeMS'])) {
            $cmd['maxTimeMS'] = $this->options['maxTimeMS'];
        }

        if (isset($this->options['readConcern']) && \MongoDB\server_supports_feature($server, self::$wireVersionForReadConcern)) {
            $cmd['readConcern'] = \MongoDB\read_concern_as_document($this->options['readConcern']);
        }

        if ($this->options['useCursor']) {
            $cmd['cursor'] = isset($this->options["batchSize"])
                ? ['batchSize' => $this->options["batchSize"]]
                : new stdClass;
        }

        return new Command($cmd);
    }
}
