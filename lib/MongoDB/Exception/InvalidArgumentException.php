<?php

namespace MongoDB\Exception;

class InvalidArgumentException extends \MongoDB\Driver\Exception\InvalidArgumentException implements Exception
{
    /**
     * Thrown when an argument or option has an invalid type.
     *
     * @param string $name         Name of the argument or option
     * @param mixed  $value        Actual value (used to derive the type)
     * @param string $expectedType Expected type
     * @return self
     */
    public static function invalidType($name, $value, $expectedType)
    {
        return new static(sprintf('Expected %s to have type "%s" but found "%s"', $name, $expectedType, is_object($value) ? get_class($value) : gettype($value)));
    }
}
