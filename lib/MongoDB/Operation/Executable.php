<?php

namespace MongoDB\Operation;

use MongoDB\Driver\Server;

/**
 * Executable interface for operation classes.
 *
 * This interface is reserved for internal use until PHPC-378 is implemented,
 * since execute() should ultimately be changed to use ServerInterface.
 *
 * @internal
 */
interface Executable
{
    /**
     * Execute the operation.
     *
     * @param Server $server
     * @return mixed
     */
    public function execute(Server $server);
}
