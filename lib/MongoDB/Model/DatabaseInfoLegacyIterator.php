<?php

namespace MongoDB\Model;

/**
 * DatabaseInfoIterator for inline listDatabases command results.
 *
 * This iterator may be used to wrap the array returned within the listDatabases
 * command's single-document result.
 *
 * @internal
 * @see MongoDB\Client::listDatabases()
 * @see http://docs.mongodb.org/manual/reference/command/listDatabases/
 */
class DatabaseInfoLegacyIterator implements DatabaseInfoIterator
{
    private $databases;

    /**
     * Constructor.
     *
     * @param array $databases
     */
    public function __construct(array $databases)
    {
        $this->databases = $databases;
    }

    /**
     * Return the current element as a DatabaseInfo instance.
     *
     * @see DatabaseInfoIterator::current()
     * @see http://php.net/iterator.current
     * @return DatabaseInfo
     */
    public function current()
    {
        return new DatabaseInfo(current($this->databases));
    }

    /**
     * Return the key of the current element.
     *
     * @see http://php.net/iterator.key
     * @return integer
     */
    public function key()
    {
        return key($this->databases);
    }

    /**
     * Move forward to next element.
     *
     * @see http://php.net/iterator.next
     */
    public function next()
    {
        next($this->databases);
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @see http://php.net/iterator.rewind
     */
    public function rewind()
    {
        reset($this->databases);
    }

    /**
     * Checks if current position is valid.
     *
     * @see http://php.net/iterator.valid
     * @return boolean
     */
    public function valid()
    {
        return key($this->databases) !== null;
    }
}
