<?php

namespace ZipStream\Exception;

use ZipStream\Exception;

/**
 * This Exception gets invoked if fread() fails on a stream.
 *
 * @author Jonatan Männchen <jonatan@maennchen.ch>
 * @copyright Copyright (c) 2014, Jonatan Männchen
 */
class StreamNotReadableException extends Exception
{
    /**
     * Constructor of the Exception
     *
     * @param String fileName - The name of the file which the stream belongs to.
     */
    public function __construct($fileName)
    {
        parent::__construct("The stream for $fileName could not be read.");
    }
}
