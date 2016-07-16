<?php

namespace MongoDB;

use MongoDB\Driver\WriteResult;
use MongoDB\Exception\BadMethodCallException;

/**
 * Result class for a delete operation.
 */
class DeleteResult
{
    private $writeResult;
    private $isAcknowledged;

    /**
     * Constructor.
     *
     * @param WriteResult $writeResult
     */
    public function __construct(WriteResult $writeResult)
    {
        $this->writeResult = $writeResult;
        $this->isAcknowledged = $writeResult->isAcknowledged();
    }

    /**
     * Return the number of documents that were deleted.
     *
     * This method should only be called if the write was acknowledged.
     *
     * @see DeleteResult::isAcknowledged()
     * @return integer
     * @throws BadMethodCallException is the write result is unacknowledged
     */
    public function getDeletedCount()
    {
        if ($this->isAcknowledged) {
            return $this->writeResult->getDeletedCount();
        }

        throw BadMethodCallException::unacknowledgedWriteResultAccess(__METHOD__);
    }

    /**
     * Return whether this delete was acknowledged by the server.
     *
     * If the delete was not acknowledged, other fields from the WriteResult
     * (e.g. deletedCount) will be undefined.
     *
     * @return boolean
     */
    public function isAcknowledged()
    {
        return $this->isAcknowledged;
    }
}
