<?php
/*
 * Copyright 2015-2017 MongoDB, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace MongoDB\Operation;

use MongoDB\Driver\Command;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\Server;
use MongoDB\Driver\WriteConcern;
use MongoDB\Driver\Exception\RuntimeException as DriverRuntimeException;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Exception\UnexpectedValueException;
use MongoDB\Exception\UnsupportedException;
use MongoDB\Model\TypeMapArrayIterator;
use ArrayIterator;
use stdClass;
use Traversable;

/**
 * Operation for the aggregate command.
 *
 * @api
 * @see \MongoDB\Collection::aggregate()
 * @see http://docs.mongodb.org/manual/reference/command/aggregate/
 */
class Aggregate implements Executable
{
    private static $wireVersionForCollation = 5;
    private static $wireVersionForCursor = 2;
    private static $wireVersionForDocumentLevelValidation = 4;
    private static $wireVersionForReadConcern = 4;
    private static $wireVersionForWriteConcern = 5;

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
     *  * collation (document): Collation specification.
     *
     *    This is not supported for server versions < 3.4 and will result in an
     *    exception at execution time if used.
     *
     *  * maxTimeMS (integer): The maximum amount of time to allow the query to
     *    run.
     *
     *  * readConcern (MongoDB\Driver\ReadConcern): Read concern. Note that a
     *    "majority" read concern is not compatible with the $out stage.
     *
     *    This is not supported for server versions < 3.2 and will result in an
     *    exception at execution time if used.
     *
     *  * readPreference (MongoDB\Driver\ReadPreference): Read preference.
     *
     *  * typeMap (array): Type map for BSON deserialization. This will be
     *    applied to the returned Cursor (it is not sent to the server).
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
     *  * writeConcern (MongoDB\Driver\WriteConcern): Write concern. This only
     *    applies when the $out stage is specified.
     *
     *    This is not supported for server versions < 3.4 and will result in an
     *    exception at execution time if used.
     *
     * @param string $databaseName   Database name
     * @param string $collectionName Collection name
     * @param array  $pipeline       List of pipeline operations
     * @param array  $options        Command options
     * @throws InvalidArgumentException for parameter/option parsing errors
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

        if (isset($options['collation']) && ! is_array($options['collation']) && ! is_object($options['collation'])) {
            throw InvalidArgumentException::invalidType('"collation" option', $options['collation'], 'array or object');
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

        if (isset($options['writeConcern']) && ! $options['writeConcern'] instanceof WriteConcern) {
            throw InvalidArgumentException::invalidType('"writeConcern" option', $options['writeConcern'], 'MongoDB\Driver\WriteConcern');
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
     * @throws UnsupportedException if collation, read concern, or write concern is used and unsupported
     * @throws DriverRuntimeException for other driver errors (e.g. connection errors)
     */
    public function execute(Server $server)
    {
        if (isset($this->options['collation']) && ! \MongoDB\server_supports_feature($server, self::$wireVersionForCollation)) {
            throw UnsupportedException::collationNotSupported();
        }

        if (isset($this->options['readConcern']) && ! \MongoDB\server_supports_feature($server, self::$wireVersionForReadConcern)) {
            throw UnsupportedException::readConcernNotSupported();
        }

        if (isset($this->options['writeConcern']) && ! \MongoDB\server_supports_feature($server, self::$wireVersionForWriteConcern)) {
            throw UnsupportedException::writeConcernNotSupported();
        }

        $isCursorSupported = \MongoDB\server_supports_feature($server, self::$wireVersionForCursor);
        $readPreference = isset($this->options['readPreference']) ? $this->options['readPreference'] : null;

        $command = $this->createCommand($server, $isCursorSupported);
        $cursor = $server->executeCommand($this->databaseName, $command, $readPreference);

        if ($isCursorSupported && $this->options['useCursor']) {
            if (isset($this->options['typeMap'])) {
                $cursor->setTypeMap($this->options['typeMap']);
            }

            return $cursor;
        }

        $result = current($cursor->toArray());

        if ( ! isset($result->result) || ! is_array($result->result)) {
            throw new UnexpectedValueException('aggregate command did not return a "result" array');
        }

        if (isset($this->options['typeMap'])) {
            return new TypeMapArrayIterator($result->result, $this->options['typeMap']);
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

        if (isset($this->options['collation'])) {
            $cmd['collation'] = (object) $this->options['collation'];
        }

        if (isset($this->options['maxTimeMS'])) {
            $cmd['maxTimeMS'] = $this->options['maxTimeMS'];
        }

        if (isset($this->options['readConcern'])) {
            $cmd['readConcern'] = \MongoDB\read_concern_as_document($this->options['readConcern']);
        }

        if (isset($this->options['writeConcern'])) {
            $cmd['writeConcern'] = \MongoDB\write_concern_as_document($this->options['writeConcern']);
        }

        if ($this->options['useCursor']) {
            $cmd['cursor'] = isset($this->options["batchSize"])
                ? ['batchSize' => $this->options["batchSize"]]
                : new stdClass;
        }

        return new Command($cmd);
    }
}
