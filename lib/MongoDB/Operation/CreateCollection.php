<?php

namespace MongoDB\Operation;

use MongoDB\Driver\Command;
use MongoDB\Driver\Server;
use MongoDB\Exception\InvalidArgumentException;

/**
 * Operation for the create command.
 *
 * @api
 * @see MongoDB\Database::createCollection()
 * @see http://docs.mongodb.org/manual/reference/command/create/
 */
class CreateCollection implements Executable
{
    const USE_POWER_OF_2_SIZES = 1;
    const NO_PADDING = 2;

    private $databaseName;
    private $collectionName;
    private $options = [];

    /**
     * Constructs a create command.
     *
     * Supported options:
     *
     *  * autoIndexId (boolean): Specify false to disable the automatic creation
     *    of an index on the _id field. For replica sets, this option cannot be
     *    false. The default is true.
     *
     *  * capped (boolean): Specify true to create a capped collection. If set,
     *    the size option must also be specified. The default is false.
     *
     *  * flags (integer): Options for the MMAPv1 storage engine only. Must be a
     *    bitwise combination CreateCollection::USE_POWER_OF_2_SIZES and
     *    CreateCollection::NO_PADDING. The default is
     *    CreateCollection::USE_POWER_OF_2_SIZES.
     *
     *  * indexOptionDefaults (document): Default configuration for indexes when
     *    creating the collection.
     *
     *  * max (integer): The maximum number of documents allowed in the capped
     *    collection. The size option takes precedence over this limit.
     *
     *  * maxTimeMS (integer): The maximum amount of time to allow the query to
     *    run.
     *
     *  * size (integer): The maximum number of bytes for a capped collection.
     *
     *  * storageEngine (document): Storage engine options.
     *
     *  * typeMap (array): Type map for BSON deserialization. This will only be
     *    used for the returned command result document.
     *
     *  * validationAction (string): Validation action.
     *
     *  * validationLevel (string): Validation level.
     *
     *  * validator (document): Validation rules or expressions.
     *
     * @see http://source.wiredtiger.com/2.4.1/struct_w_t___s_e_s_s_i_o_n.html#a358ca4141d59c345f401c58501276bbb
     * @see https://docs.mongodb.org/manual/core/document-validation/
     * @param string $databaseName   Database name
     * @param string $collectionName Collection name
     * @param array  $options        Command options
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, array $options = [])
    {
        if (isset($options['autoIndexId']) && ! is_bool($options['autoIndexId'])) {
            throw InvalidArgumentException::invalidType('"autoIndexId" option', $options['autoIndexId'], 'boolean');
        }

        if (isset($options['capped']) && ! is_bool($options['capped'])) {
            throw InvalidArgumentException::invalidType('"capped" option', $options['capped'], 'boolean');
        }

        if (isset($options['flags']) && ! is_integer($options['flags'])) {
            throw InvalidArgumentException::invalidType('"flags" option', $options['flags'], 'integer');
        }

        if (isset($options['indexOptionDefaults']) && ! is_array($options['indexOptionDefaults']) && ! is_object($options['indexOptionDefaults'])) {
            throw InvalidArgumentException::invalidType('"indexOptionDefaults" option', $options['indexOptionDefaults'], 'array or object');
        }

        if (isset($options['max']) && ! is_integer($options['max'])) {
            throw InvalidArgumentException::invalidType('"max" option', $options['max'], 'integer');
        }

        if (isset($options['maxTimeMS']) && ! is_integer($options['maxTimeMS'])) {
            throw InvalidArgumentException::invalidType('"maxTimeMS" option', $options['maxTimeMS'], 'integer');
        }

        if (isset($options['size']) && ! is_integer($options['size'])) {
            throw InvalidArgumentException::invalidType('"size" option', $options['size'], 'integer');
        }

        if (isset($options['storageEngine']) && ! is_array($options['storageEngine']) && ! is_object($options['storageEngine'])) {
            throw InvalidArgumentException::invalidType('"storageEngine" option', $options['storageEngine'], 'array or object');
        }

        if (isset($options['typeMap']) && ! is_array($options['typeMap'])) {
            throw InvalidArgumentException::invalidType('"typeMap" option', $options['typeMap'], 'array');
        }

        if (isset($options['validationAction']) && ! is_string($options['validationAction'])) {
            throw InvalidArgumentException::invalidType('"validationAction" option', $options['validationAction'], 'string');
        }

        if (isset($options['validationLevel']) && ! is_string($options['validationLevel'])) {
            throw InvalidArgumentException::invalidType('"validationLevel" option', $options['validationLevel'], 'string');
        }

        if (isset($options['validator']) && ! is_array($options['validator']) && ! is_object($options['validator'])) {
            throw InvalidArgumentException::invalidType('"validator" option', $options['validator'], 'array or object');
        }

        $this->databaseName = (string) $databaseName;
        $this->collectionName = (string) $collectionName;
        $this->options = $options;
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     * @param Server $server
     * @return array|object Command result document
     */
    public function execute(Server $server)
    {
        $cursor = $server->executeCommand($this->databaseName, $this->createCommand());

        if (isset($this->options['typeMap'])) {
            $cursor->setTypeMap($this->options['typeMap']);
        }

        return current($cursor->toArray());
    }

    /**
     * Create the create command.
     *
     * @return Command
     */
    private function createCommand()
    {
        $cmd = ['create' => $this->collectionName];

        foreach (['autoIndexId', 'capped', 'flags', 'max', 'maxTimeMS', 'size', 'validationAction', 'validationLevel'] as $option) {
            if (isset($this->options[$option])) {
                $cmd[$option] = $this->options[$option];
            }
        }

        if (isset($this->options['indexOptionDefaults'])) {
            $cmd['indexOptionDefaults'] = (object) $this->options['indexOptionDefaults'];
        }

        if (isset($this->options['storageEngine'])) {
            $cmd['storageEngine'] = (object) $this->options['storageEngine'];
        }

        if (isset($this->options['validator'])) {
            $cmd['validator'] = (object) $this->options['validator'];
        }

        return new Command($cmd);
    }
}
