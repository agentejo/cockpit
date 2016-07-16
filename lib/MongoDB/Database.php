<?php

namespace MongoDB;

use MongoDB\Collection;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Model\CollectionInfoIterator;
use MongoDB\Operation\CreateCollection;
use MongoDB\Operation\DatabaseCommand;
use MongoDB\Operation\DropCollection;
use MongoDB\Operation\DropDatabase;
use MongoDB\Operation\ListCollections;

class Database
{
    private static $defaultTypeMap = [
        'array' => 'MongoDB\Model\BSONArray',
        'document' => 'MongoDB\Model\BSONDocument',
        'root' => 'MongoDB\Model\BSONDocument',
    ];

    private $databaseName;
    private $manager;
    private $readConcern;
    private $readPreference;
    private $typeMap;
    private $writeConcern;

    /**
     * Constructs new Database instance.
     *
     * This class provides methods for database-specific operations and serves
     * as a gateway for accessing collections.
     *
     * Supported options:
     *
     *  * readConcern (MongoDB\Driver\ReadConcern): The default read concern to
     *    use for database operations and selected collections. Defaults to the
     *    Manager's read concern.
     *
     *  * readPreference (MongoDB\Driver\ReadPreference): The default read
     *    preference to use for database operations and selected collections.
     *    Defaults to the Manager's read preference.
     *
     *  * typeMap (array): Default type map for cursors and BSON documents.
     *
     *  * writeConcern (MongoDB\Driver\WriteConcern): The default write concern
     *    to use for database operations and selected collections. Defaults to
     *    the Manager's write concern.
     *
     * @param Manager $manager      Manager instance from the driver
     * @param string  $databaseName Database name
     * @param array   $options      Database options
     * @throws InvalidArgumentException
     */
    public function __construct(Manager $manager, $databaseName, array $options = [])
    {
        if (strlen($databaseName) < 1) {
            throw new InvalidArgumentException('$databaseName is invalid: ' . $databaseName);
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

        if (isset($options['writeConcern']) && ! $options['writeConcern'] instanceof WriteConcern) {
            throw InvalidArgumentException::invalidType('"writeConcern" option', $options['writeConcern'], 'MongoDB\Driver\WriteConcern');
        }

        $this->manager = $manager;
        $this->databaseName = (string) $databaseName;
        $this->readConcern = isset($options['readConcern']) ? $options['readConcern'] : $this->manager->getReadConcern();
        $this->readPreference = isset($options['readPreference']) ? $options['readPreference'] : $this->manager->getReadPreference();
        $this->typeMap = isset($options['typeMap']) ? $options['typeMap'] : self::$defaultTypeMap;
        $this->writeConcern = isset($options['writeConcern']) ? $options['writeConcern'] : $this->manager->getWriteConcern();
    }

    /**
     * Return internal properties for debugging purposes.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.debuginfo
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'databaseName' => $this->databaseName,
            'manager' => $this->manager,
            'readConcern' => $this->readConcern,
            'readPreference' => $this->readPreference,
            'typeMap' => $this->typeMap,
            'writeConcern' => $this->writeConcern,
        ];
    }

    /**
     * Select a collection within this database.
     *
     * Note: collections whose names contain special characters (e.g. ".") may
     * be selected with complex syntax (e.g. $database->{"system.profile"}) or
     * {@link selectCollection()}.
     *
     * @see http://php.net/oop5.overloading#object.get
     * @see http://php.net/types.string#language.types.string.parsing.complex
     * @param string $collectionName Name of the collection to select
     * @return Collection
     */
    public function __get($collectionName)
    {
        return $this->selectCollection($collectionName);
    }

    /**
     * Return the database name.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->databaseName;
    }

    /**
     * Execute a command on this database.
     *
     * @see DatabaseCommand::__construct() for supported options
     * @param array|object $command Command document
     * @param array        $options Options for command execution
     * @return Cursor
     * @throws InvalidArgumentException
     */
    public function command($command, array $options = [])
    {
        if ( ! isset($options['readPreference'])) {
            $options['readPreference'] = $this->readPreference;
        }

        if ( ! isset($options['typeMap'])) {
            $options['typeMap'] = $this->typeMap;
        }

        $operation = new DatabaseCommand($this->databaseName, $command, $options);
        $server = $this->manager->selectServer($options['readPreference']);

        return $operation->execute($server);
    }

    /**
     * Create a new collection explicitly.
     *
     * @see CreateCollection::__construct() for supported options
     * @param string $collectionName
     * @param array  $options
     * @return array|object Command result document
     */
    public function createCollection($collectionName, array $options = [])
    {
        if ( ! isset($options['typeMap'])) {
            $options['typeMap'] = $this->typeMap;
        }

        $operation = new CreateCollection($this->databaseName, $collectionName, $options);
        $server = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $operation->execute($server);
    }

    /**
     * Drop this database.
     *
     * @see DropDatabase::__construct() for supported options
     * @param array $options Additional options
     * @return array|object Command result document
     */
    public function drop(array $options = [])
    {
        if ( ! isset($options['typeMap'])) {
            $options['typeMap'] = $this->typeMap;
        }

        $operation = new DropDatabase($this->databaseName, $options);
        $server = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $operation->execute($server);
    }

    /**
     * Drop a collection within this database.
     *
     * @see DropCollection::__construct() for supported options
     * @param string $collectionName Collection name
     * @param array  $options        Additional options
     * @return array|object Command result document
     */
    public function dropCollection($collectionName, array $options = [])
    {
        if ( ! isset($options['typeMap'])) {
            $options['typeMap'] = $this->typeMap;
        }

        $operation = new DropCollection($this->databaseName, $collectionName, $options);
        $server = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $operation->execute($server);
    }

    /**
     * Returns the database name.
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * Returns information for all collections in this database.
     *
     * @see ListCollections::__construct() for supported options
     * @param array $options
     * @return CollectionInfoIterator
     */
    public function listCollections(array $options = [])
    {
        $operation = new ListCollections($this->databaseName, $options);
        $server = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $operation->execute($server);
    }

    /**
     * Select a collection within this database.
     *
     * @see Collection::__construct() for supported options
     * @param string $collectionName Name of the collection to select
     * @param array  $options        Collection constructor options
     * @return Collection
     */
    public function selectCollection($collectionName, array $options = [])
    {
        $options += [
            'readConcern' => $this->readConcern,
            'readPreference' => $this->readPreference,
            'typeMap' => $this->typeMap,
            'writeConcern' => $this->writeConcern,
        ];

        return new Collection($this->manager, $this->databaseName, $collectionName, $options);
    }

    /**
     * Get a clone of this database with different options.
     *
     * @see Database::__construct() for supported options
     * @param array $options Database constructor options
     * @return Database
     */
    public function withOptions(array $options = [])
    {
        $options += [
            'readConcern' => $this->readConcern,
            'readPreference' => $this->readPreference,
            'typeMap' => $this->typeMap,
            'writeConcern' => $this->writeConcern,
        ];

        return new Database($this->manager, $this->databaseName, $options);
    }
}
