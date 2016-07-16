<?php

namespace MongoDB\GridFS\Exception;

use MongoDB\Exception\RuntimeException;

class CorruptFileException extends RuntimeException
{
    /**
     * Thrown when a chunk is not found for an expected index.
     *
     * @param integer $expectedIndex Expected index number
     * @return self
     */
    public static function missingChunk($expectedIndex)
    {
        return new static(sprintf('Chunk not found for index "%d"', $expectedIndex));
    }

    /**
     * Thrown when a chunk has an unexpected index number.
     *
     * @param integer $index         Actual index number (i.e. "n" field)
     * @param integer $expectedIndex Expected index number
     * @return self
     */
    public static function unexpectedIndex($index, $expectedIndex)
    {
        return new static(sprintf('Expected chunk to have index "%d" but found "%d"', $expectedIndex, $index));
    }

    /**
     * Thrown when a chunk has an unexpected data size.
     *
     * @param integer $size         Actual size (i.e. "data" field length)
     * @param integer $expectedSize Expected size
     * @return self
     */
    public static function unexpectedSize($size, $expectedSize)
    {
        return new static(sprintf('Expected chunk to have size "%d" but found "%d"', $expectedSize, $size));
    }
}
