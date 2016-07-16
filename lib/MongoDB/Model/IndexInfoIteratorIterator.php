<?php

namespace MongoDB\Model;

use IteratorIterator;

/**
 * IndexInfoIterator for both listIndexes command and legacy query results.
 *
 * This common iterator may be used to wrap a Cursor returned by both the
 * listIndexes command and, for legacy servers, queries on the "system.indexes"
 * collection.
 *
 * @internal
 * @see MongoDB\Collection::listIndexes()
 * @see https://github.com/mongodb/specifications/blob/master/source/enumerate-indexes.rst
 * @see http://docs.mongodb.org/manual/reference/command/listIndexes/
 * @see http://docs.mongodb.org/manual/reference/system-collections/
 */
class IndexInfoIteratorIterator extends IteratorIterator implements IndexInfoIterator
{
    /**
     * Return the current element as an IndexInfo instance.
     *
     * @see IndexInfoIterator::current()
     * @see http://php.net/iterator.current
     * @return IndexInfo
     */
    public function current()
    {
        return new IndexInfo(parent::current());
    }
}
