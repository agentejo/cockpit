<?php

namespace MongoDB;

use MongoDB\Driver\WriteResult;
use MongoDB\Exception\BadMethodCallException;

/**
 * Result class for a single-document insert operation.
 */
class InsertOneResult
{
    private $writeResult;
    private $insertedId;
    private $isAcknowledged;

    /**
     * Constructor.
     *
     * @param WriteResult $writeResult
     * @param mixed       $insertedId
     */
    public function __construct(WriteResult $writeResult, $insertedId)
    {
        $this->writeResult = $writeResult;
        $this->insertedId = $insertedId;
        $this->isAcknowledged = $writeResult->isAcknowledged();
    }

    /**
     * Return the number of documents that were inserted.
     *
     * This method should only be called if the write was acknowledged.
     *
     * @see InsertOneResult::isAcknowledged()
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
     * Return the inserted document's ID.
     *
     * If the document already an ID prior to insertion (i.e. the driver did not
     * need to generate an ID), this will contain its "_id". Any
     * driver-generated ID will be an MongoDB\BSON\ObjectID instance.
     *
     * @return mixed
     */
    public function getInsertedId()
    {
        return $this->insertedId;
    }

    /**
     * Return whether this insert was acknowledged by the server.
     *
     * If the insert was not acknowledged, other fields from the WriteResult
     * (e.g. insertedCount) will be undefined.
     *
     * If the insert was not acknowledged, other fields from the WriteResult
     * (e.g. insertedCount) will be undefined and their getter methods should
     * not be invoked.
     *
     * @return boolean
     */
    public function isAcknowledged()
    {
        return $this->writeResult->isAcknowledged();
    }
}
