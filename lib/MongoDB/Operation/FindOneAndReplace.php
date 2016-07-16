<?php

namespace MongoDB\Operation;

use MongoDB\Driver\Command;
use MongoDB\Driver\Server;
use MongoDB\Exception\InvalidArgumentException;

/**
 * Operation for replacing a document with the findAndModify command.
 *
 * @api
 * @see MongoDB\Collection::findOneAndReplace()
 * @see http://docs.mongodb.org/manual/reference/command/findAndModify/
 */
class FindOneAndReplace implements Executable
{
    const RETURN_DOCUMENT_BEFORE = 1;
    const RETURN_DOCUMENT_AFTER = 2;

    private $findAndModify;

    /**
     * Constructs a findAndModify command for replacing a document.
     *
     * Supported options:
     *
     *  * bypassDocumentValidation (boolean): If true, allows the write to opt
     *    out of document level validation.
     *
     *  * maxTimeMS (integer): The maximum amount of time to allow the query to
     *    run.
     *
     *  * projection (document): Limits the fields to return for the matching
     *    document.
     *
     *  * returnDocument (enum): Whether to return the document before or after
     *    the update is applied. Must be either
     *    FindOneAndReplace::RETURN_DOCUMENT_BEFORE or
     *    FindOneAndReplace::RETURN_DOCUMENT_AFTER. The default is
     *    FindOneAndReplace::RETURN_DOCUMENT_BEFORE.
     *
     *  * sort (document): Determines which document the operation modifies if
     *    the query selects multiple documents.
     *
     *  * upsert (boolean): When true, a new document is created if no document
     *    matches the query. The default is false.
     *
     *  * writeConcern (MongoDB\Driver\WriteConcern): Write concern. This option
     *    is only supported for server versions >= 3.2.
     *
     * @param string       $databaseName   Database name
     * @param string       $collectionName Collection name
     * @param array|object $filter         Query by which to filter documents
     * @param array|object $replacement    Replacement document
     * @param array        $options        Command options
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, $filter, $replacement, array $options = [])
    {
        if ( ! is_array($filter) && ! is_object($filter)) {
            throw InvalidArgumentException::invalidType('$filter', $filter, 'array or object');
        }

        if ( ! is_array($replacement) && ! is_object($replacement)) {
            throw InvalidArgumentException::invalidType('$replacement', $replacement, 'array or object');
        }

        if (\MongoDB\is_first_key_operator($replacement)) {
            throw new InvalidArgumentException('First key in $replacement argument is an update operator');
        }

        $options += [
            'returnDocument' => self::RETURN_DOCUMENT_BEFORE,
            'upsert' => false,
        ];

        if (isset($options['projection']) && ! is_array($options['projection']) && ! is_object($options['projection'])) {
            throw InvalidArgumentException::invalidType('"projection" option', $options['projection'], 'array or object');
        }

        if ( ! is_integer($options['returnDocument'])) {
            throw InvalidArgumentException::invalidType('"returnDocument" option', $options['returnDocument'], 'integer');
        }

        if ($options['returnDocument'] !== self::RETURN_DOCUMENT_AFTER &&
            $options['returnDocument'] !== self::RETURN_DOCUMENT_BEFORE) {
            throw new InvalidArgumentException('Invalid value for "returnDocument" option: ' . $options['returnDocument']);
        }

        if (isset($options['projection'])) {
            $options['fields'] = $options['projection'];
        }

        $options['new'] = $options['returnDocument'] === self::RETURN_DOCUMENT_AFTER;

        unset($options['projection'], $options['returnDocument']);

        $this->findAndModify = new FindAndModify(
            $databaseName,
            $collectionName,
            ['query' => $filter, 'update' => $replacement] + $options
        );
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     * @param Server $server
     * @return object|null
     */
    public function execute(Server $server)
    {
        return $this->findAndModify->execute($server);
    }
}
