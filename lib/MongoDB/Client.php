<?php

namespace MongoDB;

use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadPreference;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Model\DatabaseInfoIterator;
use MongoDB\Operation\DropDatabase;
use MongoDB\Operation\ListDatabases;

class Client
{
    private static $defaultTypeMap = [
        'array' => 'MongoDB\Model\BSONArray',
        'document' => 'MongoDB\Model\BSONDocument',
        'root' => 'MongoDB\Model\BSONDocument',
    ];

    private $manager;
    private $uri;
    private $typeMap;

    /**
     * Constructs a new Client instance.
     *
     * This is the preferred class for connecting to a MongoDB server or
     * cluster of servers. It serves as a gateway for accessing individual
     * databases and collections.
     *
     * Supported driver-specific options:
     *
     *  * typeMap (array): Default type map for cursors and BSON documents.
     *
     * Other options are documented in MongoDB\Driver\Manager::__construct().
     *
     * @see http://docs.mongodb.org/manual/reference/connection-string/
     * @see http://php.net/manual/en/mongodb-driver-manager.construct.php
     * @see http://php.net/manual/en/mongodb.persistence.php#mongodb.persistence.typemaps
     * @param string $uri           MongoDB connection string
     * @param array  $uriOptions    Additional connection string options
     * @param array  $driverOptions Driver-specific options
     * @throws InvalidArgumentException
     */
    public function __construct($uri = 'mongodb://localhost:27017', array $uriOptions = [], array $driverOptions = [])
    {
        $driverOptions += ['typeMap' => self::$defaultTypeMap];

        if (isset($driverOptions['typeMap']) && ! is_array($driverOptions['typeMap'])) {
            throw InvalidArgumentException::invalidType('"typeMap" driver option', $driverOptions['typeMap'], 'array');
        }

        $this->manager = new Manager($uri, $uriOptions, $driverOptions);
        $this->uri = (string) $uri;
        $this->typeMap = isset($driverOptions['typeMap']) ? $driverOptions['typeMap'] : null;
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
            'manager' => $this->manager,
            'uri' => $this->uri,
            'typeMap' => $this->typeMap,
        ];
    }

    /**
     * Select a database.
     *
     * Note: databases whose names contain special characters (e.g. "-") may
     * be selected with complex syntax (e.g. $client->{"that-database"}) or
     * {@link selectDatabase()}.
     *
     * @see http://php.net/oop5.overloading#object.get
     * @see http://php.net/types.string#language.types.string.parsing.complex
     * @param string $databaseName Name of the database to select
     * @return Database
     */
    public function __get($databaseName)
    {
        return $this->selectDatabase($databaseName);
    }

    /**
     * Return the connection string (i.e. URI).
     *
     * @return string
     */
    public function __toString()
    {
        return $this->uri;
    }

    /**
     * Drop a database.
     *
     * @see DropDatabase::__construct() for supported options
     * @param string $databaseName Database name
     * @param array  $options      Additional options
     * @return array|object Command result document
     */
    public function dropDatabase($databaseName, array $options = [])
    {
        if ( ! isset($options['typeMap'])) {
            $options['typeMap'] = $this->typeMap;
        }

        $operation = new DropDatabase($databaseName, $options);
        $server = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $operation->execute($server);
    }

    /**
     * List databases.
     *
     * @see ListDatabases::__construct() for supported options
     * @return DatabaseInfoIterator
     */
    public function listDatabases(array $options = [])
    {
        $operation = new ListDatabases($options);
        $server = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $operation->execute($server);
    }

    /**
     * Select a collection.
     *
     * @see Collection::__construct() for supported options
     * @param string $databaseName   Name of the database containing the collection
     * @param string $collectionName Name of the collection to select
     * @param array  $options        Collection constructor options
     * @return Collection
     */
    public function selectCollection($databaseName, $collectionName, array $options = [])
    {
        $options += ['typeMap' => $this->typeMap];

        return new Collection($this->manager, $databaseName, $collectionName, $options);
    }

    /**
     * Select a database.
     *
     * @see Database::__construct() for supported options
     * @param string $databaseName Name of the database to select
     * @param array  $options      Database constructor options
     * @return Database
     */
    public function selectDatabase($databaseName, array $options = [])
    {
        $options += ['typeMap' => $this->typeMap];

        return new Database($this->manager, $databaseName, $options);
    }
}
