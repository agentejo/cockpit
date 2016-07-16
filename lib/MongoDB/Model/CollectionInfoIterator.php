<?php

namespace MongoDB\Model;

use Iterator;

/**
 * CollectionInfoIterator interface.
 *
 * This iterator is used for enumerating collections in a database.
 *
 * @api
 * @see MongoDB\Database::listCollections()
 */
interface CollectionInfoIterator extends Iterator
{
    /**
     * Return the current element as a CollectionInfo instance.
     *
     * @return CollectionInfo
     */
    public function current();
}
