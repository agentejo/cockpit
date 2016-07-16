<?php

namespace MongoDB\Model;

use FilterIterator;
use Iterator;
use IteratorIterator;
use Traversable;

/**
 * CollectionInfoIterator for legacy "system.namespaces" query results.
 *
 * This iterator may be used to wrap a Cursor returned for queries on the
 * "system.namespaces" collection. It includes logic to filter out internal
 * collections and modify the collection name to be consistent with results from
 * the listCollections command.
 *
 * @internal
 * @see MongoDB\Database::listCollections()
 * @see https://github.com/mongodb/specifications/blob/master/source/enumerate-collections.rst
 * @see http://docs.mongodb.org/manual/reference/command/listCollections/
 * @see http://docs.mongodb.org/manual/reference/system-collections/
 */
class CollectionInfoLegacyIterator extends FilterIterator implements CollectionInfoIterator
{
    /**
     * Constructor.
     *
     * @param Traversable $iterator
     */
    public function __construct(Traversable $iterator)
    {
        /* FilterIterator requires an Iterator, so wrap all other Traversables
         * with an IteratorIterator as a convenience.
         */
        if ( ! $iterator instanceof Iterator) {
            $iterator = new IteratorIterator($iterator);
        }

        parent::__construct($iterator);
    }

    /**
     * Filter out internal or invalid collections.
     *
     * @see http://php.net/filteriterator.accept
     * @return boolean
     */
    public function accept()
    {
        $info = parent::current();

        if ( ! isset($info['name']) || ! is_string($info['name'])) {
            return false;
        }

        // Reject names with "$" characters (e.g. indexes, oplog)
        if (strpos($info['name'], '$') !== false) {
            return false;
        }

        $firstDot = strpos($info['name'], '.');

        /* Legacy collection names are a namespace and should be prefixed with
         * the database name and a dot. Reject values that omit this prefix or
         * are empty beyond it.
         */
        if ($firstDot === false || $firstDot + 1 == strlen($info['name'])) {
            return false;
        }

        return true;
    }

    /**
     * Return the current element as a CollectionInfo instance.
     *
     * @see CollectionInfoIterator::current()
     * @see http://php.net/iterator.current
     * @return CollectionInfo
     */
    public function current()
    {
        $info = parent::current();

        // Trim the database prefix up to and including the first dot
        $firstDot = strpos($info['name'], '.');

        if ($firstDot !== false) {
            $info['name'] = (string) substr($info['name'], $firstDot + 1);
        }

        return new CollectionInfo($info);
    }
}
