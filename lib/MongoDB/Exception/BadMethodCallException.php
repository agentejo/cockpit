<?php

namespace MongoDB\Exception;

class BadMethodCallException extends \BadMethodCallException implements Exception
{
    /**
     * Thrown when a mutable method is invoked on an immutable object.
     *
     * @param string $class Class name
     * @return self
     */
    public static function classIsImmutable($class)
    {
        return new static(sprintf('%s is immutable', $class));
    }

    /**
     * Thrown when accessing a result field on an unacknowledged write result.
     *
     * @param string $method Method name
     * @return self
     */
    public static function unacknowledgedWriteResultAccess($method)
    {
        return new static(sprintf('%s should not be called for an unacknowledged write result', $method));
    }
}
