<?php

namespace MongoDB\GridFS\Exception;

use MongoDB\Exception\RuntimeException;

class FileNotFoundException extends RuntimeException
{
    /**
     * Thrown when a file cannot be found by its filename and revision.
     *
     * @param string  $filename  Filename
     * @param integer $revision  Revision
     * @param string  $namespace Namespace for the files collection
     * @return self
     */
    public static function byFilenameAndRevision($filename, $revision, $namespace)
    {
        return new static(sprintf('File with name "%s" and revision "%d" not found in "%s"', $filename, $revision, $namespace));
    }

    /**
     * Thrown when a file cannot be found by its ID.
     *
     * @param mixed  $id        File ID
     * @param string $namespace Namespace for the files collection
     * @return self
     */
    public static function byId($id, $namespace)
    {
        $json = \MongoDB\BSON\toJSON(\MongoDB\BSON\fromPHP(['_id' => $id]));

        return new static(sprintf('File "%s" not found in "%s"', $json, $namespace));
    }
}
