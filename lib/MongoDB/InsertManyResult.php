<?php

namespace MongoDB;

use MongoDB\Driver\WriteResult;
use MongoDB\Exception\BadMethodCallException;

/**
 * Result class for a multi-document insert operation.
 */
class InsertManyResult
{
    private $writeResult;
    private $insertedIds;
    private $isAcknowledged;

    /**
     * Constructor.
     *
     * @param WriteResult $writeResult
     * @param mixed[]     $insertedIds
     */
    public function __construct(WriteResult $writeResult, array $insertedIds)
    {
        $this->writeResult = $writeResult;
        $this->insertedIds = $insertedIds;
        $this->isAcknowledged = $writeResult->isAcknowledged();
    }

    /**
     * Return the number of documents that were inserted.
     *
     * This method should only be called if the write was acknowledged.
     *
     * @see InsertManyResult::isAcknowledged()
     * @return integer
     * @throws BadMethodCallException is the write result is unacknowledged
     */
    public function getInsertedCount()
    {
        if ($this->isAcknowledged) {
            return $this->writeResult->getInsertedCount();
        }

        throw BadMethodCallException::unacknowledgedWriteResultAccess(__METHOD__);
    }

    /**
     * Return a map of the inserted documents' IDs.
     *
     * The index of each ID in the map corresponds to the document's position in
     * the bulk operation. If the document had an ID prior to insertion (i.e.
     * the driver did not generate an ID), this will contain its "_id" field
     * value. Any driver-generated ID will be an MongoDB\BSON\ObjectID instance.
     *
     * @return mixed[]
     */
    public function getInsertedIds()
    {
        return $this->insertedIds;
    }

    /**
     * Return whether this insert result was acknowledged by the server.
     *
     * If the insert was not acknowledged, other fields from the WriteResult
     * (e.g. insertedCount) will be undefined.
     *
     * @return boolean
     */
    public function isAcknowledged()
    {
        return $this->writeResult->isAcknowledged();
    }
}
