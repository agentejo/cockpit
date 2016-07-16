<?php

namespace MongoDB\Model;

use Iterator;

/**
 * DatabaseInfoIterator interface.
 *
 * This iterator is used for enumerating databases on a server.
 *
 * @api
 * @see MongoDB\Client::listDatabases()
 */
interface DatabaseInfoIterator extends Iterator
{
    /**
     * Return the current element as a DatabaseInfo instance.
     *
     * @return DatabaseInfo
     */
    public function current();
}
