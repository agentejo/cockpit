<?php
/*
 * Copyright 2017 MongoDB, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace MongoDB;

use MongoDB\BSON\Serializable;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\Exception\ConnectionTimeoutException;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Exception\ResumeTokenException;
use IteratorIterator;
use Iterator;

/**
 * Iterator for the changeStream command.
 *
 * @api
 * @see \MongoDB\Collection::changeStream()
 * @see http://docs.mongodb.org/manual/reference/command/changeStream/
 */
class ChangeStream implements Iterator
{
    private $resumeToken;
    private $resumeCallable;
    private $csIt;
    private $key;

    const CURSOR_NOT_FOUND = 43;

    /**
     * @param Cursor $cursor
     * @param callable $resumeCallable
     */
    public function __construct(Cursor $cursor, callable $resumeCallable)
    {
        $this->resumeCallable = $resumeCallable;
        $this->csIt = new IteratorIterator($cursor);

        $this->key = 0;
    }

    /**
     * @see http://php.net/iterator.current
     * @return mixed
     */
    public function current()
    {
        return $this->csIt->current();
    }

    /**
     * @return \MongoDB\Driver\CursorId
     */
    public function getCursorId()
    {
        return $this->csIt->getInnerIterator()->getId();
    }

    /**
     * @see http://php.net/iterator.key
     * @return mixed
     */
    public function key()
    {
        if ($this->valid()) {
            return $this->key;
        }
        return null;
    }

    /**
     * @see http://php.net/iterator.next
     * @return void
     */
    public function next()
    {
        $resumable = false;
        try {
            $this->csIt->next();
            if ($this->valid()) {
                $this->resumeToken = $this->extractResumeToken($this->csIt->current());
                $this->key++;
            }
        } catch (RuntimeException $e) {
            if (strpos($e->getMessage(), "not master") !== false) {
                $resumable = true;
            }
            if ($e->getCode() === self::CURSOR_NOT_FOUND) {
                $resumable = true;
            }
            if ($e instanceof ConnectionTimeoutException) {
                $resumable = true;
            }
        }
        if ($resumable) {
            $this->resume();
        }
    }

    /**
     * @see http://php.net/iterator.rewind
     * @return void
     */
    public function rewind()
    {
        $resumable = false;
        try {
            $this->csIt->rewind();
            if ($this->valid()) {
                $this->resumeToken = $this->extractResumeToken($this->csIt->current());
            }
        } catch (RuntimeException $e) {
            if (strpos($e->getMessage(), "not master") !== false) {
                $resumable = true;
            }
            if ($e->getCode() === self::CURSOR_NOT_FOUND) {
                $resumable = true;
            }
            if ($e instanceof ConnectionTimeoutException) {
                $resumable = true;
            }
        }
        if ($resumable) {
            $this->resume();
        }
    }

    /**
     * @see http://php.net/iterator.valid
     * @return boolean
     */
    public function valid()
    {
        return $this->csIt->valid();
    }

    /**
     * Extracts the resume token (i.e. "_id" field) from the change document.
     *
     * @param array|document $document Change document
     * @return mixed
     * @throws InvalidArgumentException
     * @throws ResumeTokenException if the resume token is not found or invalid
     */
    private function extractResumeToken($document)
    {
        if ( ! is_array($document) && ! is_object($document)) {
            throw InvalidArgumentException::invalidType('$document', $document, 'array or object');
        }

        if ($document instanceof Serializable) {
            return $this->extractResumeToken($document->bsonSerialize());
        }

        $resumeToken = is_array($document)
            ? (isset($document['_id']) ? $document['_id'] : null)
            : (isset($document->_id) ? $document->_id : null);

        if ( ! isset($resumeToken)) {
            throw ResumeTokenException::notFound();
        }

        if ( ! is_array($resumeToken) && ! is_object($resumeToken)) {
            throw ResumeTokenException::invalidType($resumeToken);
        }

        return $resumeToken;
    }

    /**
     * Creates a new changeStream after a resumable server error.
     *
     * @return void
     */
    private function resume()
    {
        $newChangeStream = call_user_func($this->resumeCallable, $this->resumeToken);
        $this->csIt = $newChangeStream->csIt;
        $this->csIt->rewind();
    }
}
