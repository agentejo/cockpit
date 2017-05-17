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
use MongoDB\Driver\Server;
use MongoDB\Driver\WriteConcern;
use MongoDB\Driver\Exception\RuntimeException as DriverRuntimeException;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Exception\UnexpectedValueException;
use MongoDB\Exception\UnsupportedException;

/**
 * Operation for the findAndModify command.
 *
 * This class is used internally by the FindOneAndDelete, FindOneAndReplace, and
 * FindOneAndUpdate operation classes.
 *
 * @internal
 * @see http://docs.mongodb.org/manual/reference/command/findAndModify/
 */
class FindAndModify implements Executable
{
    private static $wireVersionForCollation = 5;
    private static $wireVersionForDocumentLevelValidation = 4;
    private static $wireVersionForWriteConcern = 4;

    private $databaseName;
    private $collectionName;
    private $options;

    /**
     * Constructs a findAndModify command.
     *
     * Supported options:
     *
     *  * collation (document): Collation specification.
     *
     *    This is not supported for server versions < 3.4 and will result in an
     *    exception at execution time if used.
     *
     *  * bypassDocumentValidation (boolean): If true, allows the write to opt
     *    out of document level validation.
     *
     *  * fields (document): Limits the fields to return for the matching
     *    document.
     *
     *  * maxTimeMS (integer): The maximum amount of time to allow the query to
     *    run.
     *
     *  * new (boolean): When true, returns the modified document rather than
     *    the original. This option is ignored for remove operations. The
     *    The default is false.
     *
     *  * query (document): Query by which to filter documents.
     *
     *  * remove (boolean): When true, removes the matched document. This option
     *    cannot be true if the update option is set. The default is false.
     *
     *  * sort (document): Determines which document the operation modifies if
     *    the query selects multiple documents.
     *
     *  * typeMap (array): Type map for BSON deserialization.
     *
     *  * update (document): Update or replacement to apply to the matched
     *    document. This option cannot be set if the remove option is true.
     *
     *  * upsert (boolean): When true, a new document is created if no document
     *    matches the query. This option is ignored for remove operations. The
     *    default is false.
     *
     *  * writeConcern (MongoDB\Driver\WriteConcern): Write concern.
     *
     *    This is not supported for server versions < 3.2 and will result in an
     *    exception at execution time if used.
     *
     * @param string $databaseName   Database name
     * @param string $collectionName Collection name
     * @param array  $options        Command options
     * @throws InvalidArgumentException for parameter/option parsing errors
     */
    public function __construct($databaseName, $collectionName, array $options)
    {
        $options += [
            'new' => false,
            'remove' => false,
            'upsert' => false,
        ];

        if (isset($options['bypassDocumentValidation']) && ! is_bool($options['bypassDocumentValidation'])) {
            throw InvalidArgumentException::invalidType('"bypassDocumentValidation" option', $options['bypassDocumentValidation'], 'boolean');
        }

        if (isset($options['collation']) && ! is_array($options['collation']) && ! is_object($options['collation'])) {
            throw InvalidArgumentException::invalidType('"collation" option', $options['collation'], 'array or object');
        }

        if (isset($options['fields']) && ! is_array($options['fields']) && ! is_object($options['fields'])) {
            throw InvalidArgumentException::invalidType('"fields" option', $options['fields'], 'array or object');
        }

        if (isset($options['maxTimeMS']) && ! is_integer($options['maxTimeMS'])) {
            throw InvalidArgumentException::invalidType('"maxTimeMS" option', $options['maxTimeMS'], 'integer');
        }

        if ( ! is_bool($options['new'])) {
            throw InvalidArgumentException::invalidType('"new" option', $options['new'], 'boolean');
        }

        if (isset($options['query']) && ! is_array($options['query']) && ! is_object($options['query'])) {
            throw InvalidArgumentException::invalidType('"query" option', $options['query'], 'array or object');
        }

        if ( ! is_bool($options['remove'])) {
            throw InvalidArgumentException::invalidType('"remove" option', $options['remove'], 'boolean');
        }

        if (isset($options['sort']) && ! is_array($options['sort']) && ! is_object($options['sort'])) {
            throw InvalidArgumentException::invalidType('"sort" option', $options['sort'], 'array or object');
        }

        if (isset($options['typeMap']) && ! is_array($options['typeMap'])) {
            throw InvalidArgumentException::invalidType('"typeMap" option', $options['typeMap'], 'array');
        }

        if (isset($options['update']) && ! is_array($options['update']) && ! is_object($options['update'])) {
            throw InvalidArgumentException::invalidType('"update" option', $options['update'], 'array or object');
        }

        if (isset($options['writeConcern']) && ! $options['writeConcern'] instanceof WriteConcern) {
            throw InvalidArgumentException::invalidType('"writeConcern" option', $options['writeConcern'], 'MongoDB\Driver\WriteConcern');
        }

        if ( ! is_bool($options['upsert'])) {
            throw InvalidArgumentException::invalidType('"upsert" option', $options['upsert'], 'boolean');
        }

        if ( ! (isset($options['update']) xor $options['remove'])) {
            throw new InvalidArgumentException('The "remove" option must be true or an "update" document must be specified, but not both');
        }

        $this->databaseName = (string) $databaseName;
        $this->collectionName = (string) $collectionName;
        $this->options = $options;
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     * @param Server $server
     * @return array|object|null
     * @throws UnexpectedValueException if the command response was malformed
     * @throws UnsupportedException if collation or write concern is used and unsupported
     * @throws DriverRuntimeException for other driver errors (e.g. connection errors)
     */
    public function execute(Server $server)
    {
        if (isset($this->options['collation']) && ! \MongoDB\server_supports_feature($server, self::$wireVersionForCollation)) {
            throw UnsupportedException::collationNotSupported();
        }

        if (isset($this->options['writeConcern']) && ! \MongoDB\server_supports_feature($server, self::$wireVersionForWriteConcern)) {
            throw UnsupportedException::writeConcernNotSupported();
        }

        $cursor = $server->executeCommand($this->databaseName, $this->createCommand($server));
        $result = current($cursor->toArray());

        if ( ! isset($result->value)) {
            return null;
        }

        /* Prior to 3.0, findAndModify returns an empty document instead of null
         * when an upsert is performed and the pre-modified document was
         * requested.
         */
        if ($this->options['upsert'] && ! $this->options['new'] &&
            isset($result->lastErrorObject->updatedExisting) &&
            ! $result->lastErrorObject->updatedExisting) {

            return null;
        }

        if ( ! is_object($result->value)) {
            throw new UnexpectedValueException('findAndModify command did not return a "value" document');
        }

        if (isset($this->options['typeMap'])) {
            return \MongoDB\apply_type_map_to_document($result->value, $this->options['typeMap']);
        }

        return $result->value;
    }

    /**
     * Create the findAndModify command.
     *
     * @param Server $server
     * @return Command
     */
    private function createCommand(Server $server)
    {
        $cmd = ['findAndModify' => $this->collectionName];

        if ($this->options['remove']) {
            $cmd['remove'] = true;
        } else {
            $cmd['new'] = $this->options['new'];
            $cmd['upsert'] = $this->options['upsert'];
        }

        foreach (['collation', 'fields', 'query', 'sort', 'update'] as $option) {
            if (isset($this->options[$option])) {
                $cmd[$option] = (object) $this->options[$option];
            }
        }

        if (isset($this->options['maxTimeMS'])) {
            $cmd['maxTimeMS'] = $this->options['maxTimeMS'];
        }

        if (isset($this->options['bypassDocumentValidation']) && \MongoDB\server_supports_feature($server, self::$wireVersionForDocumentLevelValidation)) {
            $cmd['bypassDocumentValidation'] = $this->options['bypassDocumentValidation'];
        }

        if (isset($this->options['writeConcern'])) {
            $cmd['writeConcern'] = \MongoDB\write_concern_as_document($this->options['writeConcern']);
        }

        return new Command($cmd);
    }
}
