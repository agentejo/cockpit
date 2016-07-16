<?php

namespace MongoDB\Model;

/**
 * Database information model class.
 *
 * This class models the database information returned by the listDatabases
 * command. It provides methods to access common database properties.
 *
 * @api
 * @see MongoDB\Client::listDatabases()
 * @see http://docs.mongodb.org/manual/reference/command/listDatabases/
 */
class DatabaseInfo
{
    private $info;

    /**
     * Constructor.
     *
     * @param array $info Database info
     */
    public function __construct(array $info)
    {
        $this->info = $info;
    }

    /**
     * Return the collection info as an array.
     *
     * @see http://php.net/oop5.magic#language.oop5.magic.debuginfo
     * @return array
     */
    public function __debugInfo()
    {
        return $this->info;
    }

    /**
     * Return the database name.
     *
     * @return string
     */
    public function getName()
    {
        return (string) $this->info['name'];
    }

    /**
     * Return the databases size on disk (in bytes).
     *
     * @return integer
     */
    public function getSizeOnDisk()
    {
        return (integer) $this->info['sizeOnDisk'];
    }

    /**
     * Return whether the database is empty.
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return (boolean) $this->info['empty'];
    }
}
