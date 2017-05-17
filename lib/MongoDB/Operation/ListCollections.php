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
use MongoDB\Driver\Query;
use MongoDB\Driver\Server;
use MongoDB\Driver\Exception\RuntimeException as DriverRuntimeException;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Model\CollectionInfoCommandIterator;
use MongoDB\Model\CollectionInfoIterator;
use MongoDB\Model\CollectionInfoLegacyIterator;

/**
 * Operation for the listCollections command.
 *
 * @api
 * @see \MongoDB\Database::listCollections()
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
     * @throws InvalidArgumentException for parameter/option parsing errors
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
     * @throws InvalidArgumentException if filter.name is not a string for legacy execution
     * @throws DriverRuntimeException for other driver errors (e.g. connection errors)
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
     * @throws DriverRuntimeException for other driver errors (e.g. connection errors)
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
     * @throws InvalidArgumentException if filter.name is not a string
     * @throws DriverRuntimeException for other driver errors (e.g. connection errors)
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
