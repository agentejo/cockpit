<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MongoLite;

/**
 * Client object.
 */
class Client {

    /**
     * @var array
     */
    protected $databases = [];

    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor
     *
     * @param string $path - Pathname to database file or :memory:
     * @param array  $options
     */
    public function __construct($path, $options = []) {
        $this->path    = \rtrim($path, '\\');
        $this->options = $options;
    }

    /**
     * List Databases
     *
     * @return array List of database names
     */
    public function listDBs() {

        // Return all databases available in memory
        if ($this->path === Database::DSN_PATH_MEMORY) {
            return array_keys($this->databases);
        }

        // Return all databases available on disk
        $databases = [];

        foreach (new \DirectoryIterator($this->path) as $fileInfo) {
            if ($fileInfo->getExtension() === 'sqlite') {
                $databases[] = $fileInfo->getBasename('.sqlite');
            }
        }

        return $databases;
    }

    /**
     * Select Collection
     *
     * @param  string $database
     * @param  string $collection
     * @return Collection
     */
    public function selectCollection($database, $collection) {

        return $this->selectDB($database)->selectCollection($collection);
    }

    /**
     * Select database
     *
     * @param  string $name
     * @return Database
     */
    public function selectDB($name) {

        if (!isset($this->databases[$name])) {
            $this->databases[$name] = new Database(
                $this->path === Database::DSN_PATH_MEMORY ? $this->path : sprintf('%s/%s.sqlite', $this->path, $name),
                $this->options
            );
        }

        return $this->databases[$name];
    }

    public function __get($database) {

        return $this->selectDB($database);
    }
}
