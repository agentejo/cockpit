<?php

namespace MongoDB\GridFS;

use MongoDB\BSON\Binary;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Exception\Exception as DriverException;
use MongoDB\Exception\InvalidArgumentException;

/**
 * WritableStream abstracts the process of writing a GridFS file.
 *
 * @internal
 */
class WritableStream
{
    private static $defaultChunkSizeBytes = 261120;

    private $buffer;
    private $bufferLength = 0;
    private $chunkOffset = 0;
    private $chunkSize;
    private $collectionWrapper;
    private $ctx;
    private $file;
    private $isClosed = false;
    private $length = 0;

    /**
     * Constructs a writable GridFS stream.
     *
     * Supported options:
     *
     *  * _id (mixed): File document identifier. Defaults to a new ObjectId.
     *
     *  * aliases (array of strings): DEPRECATED An array of aliases. 
     *    Applications wishing to store aliases should add an aliases field to
     *    the metadata document instead.
     *
     *  * chunkSizeBytes (integer): The chunk size in bytes. Defaults to
     *    261120 (i.e. 255 KiB).
     *
     *  * contentType (string): DEPRECATED content type to be stored with the
     *    file. This information should now be added to the metadata.
     *
     *  * metadata (document): User data for the "metadata" field of the files
     *    collection document.
     *
     * @param CollectionWrapper $collectionWrapper GridFS collection wrapper
     * @param string            $filename          Filename
     * @param array             $options           Upload options
     * @throws InvalidArgumentException
     */
    public function __construct(CollectionWrapper $collectionWrapper, $filename, array $options = [])
    {
        $options += [
            '_id' => new ObjectId,
            'chunkSizeBytes' => self::$defaultChunkSizeBytes,
        ];

        if (isset($options['aliases']) && ! \MongoDB\is_string_array($options['aliases'])) {
            throw InvalidArgumentException::invalidType('"aliases" option', $options['aliases'], 'array of strings');
        }

        if (isset($options['chunkSizeBytes']) && ! is_integer($options['chunkSizeBytes'])) {
            throw InvalidArgumentException::invalidType('"chunkSizeBytes" option', $options['chunkSizeBytes'], 'integer');
        }

        if (isset($options['contentType']) && ! is_string($options['contentType'])) {
            throw InvalidArgumentException::invalidType('"contentType" option', $options['contentType'], 'string');
        }

        if (isset($options['metadata']) && ! is_array($options['metadata']) && ! is_object($options['metadata'])) {
            throw InvalidArgumentException::invalidType('"metadata" option', $options['metadata'], 'array or object');
        }

        $this->chunkSize = $options['chunkSizeBytes'];
        $this->collectionWrapper = $collectionWrapper;
        $this->buffer = fopen('php://temp', 'w+');
        $this->ctx = hash_init('md5');

        $this->file = [
            '_id' => $options['_id'],
            'chunkSize' => $this->chunkSize,
            'filename' => (string) $filename,
            // TODO: This is necessary until PHPC-536 is implemented
            'uploadDate' => new UTCDateTime(floor(microtime(true) * 1000)),
        ] + array_intersect_key($options, ['aliases' => 1, 'contentType' => 1, 'metadata' => 1]);
    }

    /**
     * Return internal properties for debugging purposes.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.debuginfo
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'bucketName' => $this->collectionWrapper->getBucketName(),
            'databaseName' => $this->collectionWrapper->getDatabaseName(),
            'file' => $this->file,
        ];
    }

    /**
     * Closes an active stream and flushes all buffered data to GridFS.
     */
    public function close()
    {
        if ($this->isClosed) {
            // TODO: Should this be an error condition? e.g. BadMethodCallException
            return;
        }

        rewind($this->buffer);
        $cached = stream_get_contents($this->buffer);

        if (strlen($cached) > 0) {
            $this->insertChunk($cached);
        }

        fclose($this->buffer);
        $this->fileCollectionInsert();
        $this->isClosed = true;
    }

    /**
     * Return the stream's file document.
     *
     * @return stdClass
     */
    public function getFile()
    {
        return (object) $this->file;
    }

    /**
     * Return the stream's size in bytes.
     *
     * Note: this value will increase as more data is written to the stream.
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->length;
    }

    /**
     * Inserts binary data into GridFS via chunks.
     *
     * Data will be buffered internally until chunkSizeBytes are accumulated, at
     * which point a chunk's worth of data will be inserted and the buffer
     * reset.
     *
     * @param string $toWrite Binary data to write
     * @return integer
     */
    public function insertChunks($toWrite)
    {
        if ($this->isClosed) {
            // TODO: Should this be an error condition? e.g. BadMethodCallException
            return;
        }

        $readBytes = 0;

        while ($readBytes != strlen($toWrite)) {
            $addToBuffer = substr($toWrite, $readBytes, $this->chunkSize - $this->bufferLength);
            fwrite($this->buffer, $addToBuffer);
            $readBytes += strlen($addToBuffer);
            $this->bufferLength += strlen($addToBuffer);

            if ($this->bufferLength == $this->chunkSize) {
                rewind($this->buffer);
                $this->insertChunk(stream_get_contents($this->buffer));
                ftruncate($this->buffer, 0);
                $this->bufferLength = 0;
            }
        }

        return $readBytes;
    }

    private function abort()
    {
        $this->collectionWrapper->deleteChunksByFilesId($this->file['_id']);
        $this->isClosed = true;
    }

    private function fileCollectionInsert()
    {
        if ($this->isClosed) {
            // TODO: Should this be an error condition? e.g. BadMethodCallException
            return;
        }

        $md5 = hash_final($this->ctx);

        $this->file['length'] = $this->length;
        $this->file['md5'] = $md5;

        $this->collectionWrapper->insertFile($this->file);

        return $this->file['_id'];
    }

    private function insertChunk($data)
    {
        if ($this->isClosed) {
            // TODO: Should this be an error condition? e.g. BadMethodCallException
            return;
        }

        $toUpload = [
            'files_id' => $this->file['_id'],
            'n' => $this->chunkOffset,
            'data' => new Binary($data, Binary::TYPE_GENERIC),
        ];

        hash_update($this->ctx, $data);

        $this->collectionWrapper->insertChunk($toUpload);
        $this->length += strlen($data);
        $this->chunkOffset++;
    }

    private function readChunk($source)
    {
        try {
            $data = fread($source, $this->chunkSize);
        } catch (DriverException $e) {
            $this->abort();
            throw $e;
        }

        return $data;
    }
}
