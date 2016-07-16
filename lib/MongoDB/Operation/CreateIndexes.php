<?php

namespace MongoDB\Operation;

use MongoDB\Driver\Command;
use MongoDB\Driver\Server;
use MongoDB\Driver\BulkWrite as Bulk;
use MongoDB\Driver\WriteConcern;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Model\IndexInput;

/**
 * Operation for the createIndexes command.
 *
 * @api
 * @see MongoDB\Collection::createIndex()
 * @see MongoDB\Collection::createIndexes()
 * @see http://docs.mongodb.org/manual/reference/command/createIndexes/
 */
class CreateIndexes implements Executable
{
    private static $wireVersionForCommand = 2;

    private $databaseName;
    private $collectionName;
    private $indexes = [];

    /**
     * Constructs a createIndexes command.
     *
     * @param string  $databaseName   Database name
     * @param string  $collectionName Collection name
     * @param array[] $indexes        List of index specifications
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, array $indexes)
    {
        if (empty($indexes)) {
            throw new InvalidArgumentException('$indexes is empty');
        }

        $expectedIndex = 0;

        foreach ($indexes as $i => $index) {
            if ($i !== $expectedIndex) {
                throw new InvalidArgumentException(sprintf('$indexes is not a list (unexpected index: "%s")', $i));
            }

            if ( ! is_array($index)) {
                throw InvalidArgumentException::invalidType(sprintf('$index[%d]', $i), $index, 'array');
            }

            if ( ! isset($index['ns'])) {
                $index['ns'] = $databaseName . '.' . $collectionName;
            }

            $this->indexes[] = new IndexInput($index);

            $expectedIndex += 1;
        }

        $this->databaseName = (string) $databaseName;
        $this->collectionName = (string) $collectionName;
    }

    /**
     * Execute the operation.
     *
     * For servers < 2.6, this will actually perform an insert operation on the
     * database's "system.indexes" collection.
     *
     * @see Executable::execute()
     * @param Server $server
     * @return string[] The names of the created indexes
     */
    public function execute(Server $server)
    {
        if (\MongoDB\server_supports_feature($server, self::$wireVersionForCommand)) {
            $this->executeCommand($server);
        } else {
            $this->executeLegacy($server);
        }

        return array_map(function(IndexInput $index) { return (string) $index; }, $this->indexes);
    }

    /**
     * Create one or more indexes for the collection using the createIndexes
     * command.
     *
     * @param Server $server
     */
    private function executeCommand(Server $server)
    {
        $command = new Command([
            'createIndexes' => $this->collectionName,
            'indexes' => $this->indexes,
        ]);

        $server->executeCommand($this->databaseName, $command);
    }

    /**
     * Create one or more indexes for the collection by inserting into the
     * "system.indexes" collection (MongoDB <2.6).
     *
     * @param Server $server
     */
    private function executeLegacy(Server $server)
    {
        $bulk = new Bulk(['ordered' => true]);

        foreach ($this->indexes as $index) {
            $bulk->insert($index);
        }

        $server->executeBulkWrite($this->databaseName . '.system.indexes', $bulk, new WriteConcern(1));
    }
}
