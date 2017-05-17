<?php
/*
 * Copyright 2016-2017 MongoDB, Inc.
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

namespace MongoDB\Model;

use ArrayIterator;

/**
 * Iterator for applying a type map to documents in inline command results.
 *
 * This iterator may be used to apply a type map to an array of documents
 * returned by a database command (e.g. aggregate on servers < 2.6) and allows
 * for functional equivalence with commands that return their results via a
 * cursor (e.g. aggregate on servers >= 2.6).
 *
 * @internal
 */
class TypeMapArrayIterator extends ArrayIterator
{
    private $typeMap;

    /**
     * Constructor.
     *
     * @param array $documents
     * @param array $typeMap
     */
    public function __construct(array $documents = [], array $typeMap)
    {
        parent::__construct($documents);

        $this->typeMap = $typeMap;
    }

    /**
     * Return the current element with the type map applied to it.
     *
     * @see http://php.net/arrayiterator.current
     * @return array|object
     */
    public function current()
    {
        return \MongoDB\apply_type_map_to_document(parent::current(), $this->typeMap);
    }
}
