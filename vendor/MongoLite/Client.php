<?php

namespace MongoLite;

/**
 * Client object.
 */
class Client {

    /**
     * @var array
     */
    protected $databases = array();
    
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
     * @param string $path   
     * @param array  $options
     */
    public function __construct($path, $options=array()) {
        $this->path    = rtrim($path, '\\');
        $this->options = $options;
    }

    /**
     * List Databases
     * 
     * @return array List of databases
     */
    public function listDBs() {
        
        $databases = array();

        foreach (new \DirectoryIterator($this->path) as $fileInfo) {
            if(preg_match('/\.sqlite$/', $fileInfo->getFilename())) {
                $databases[] = str_replace(".sqlite", "", $fileInfo->getFilename());
             }
        }

        return $databases;
    }

    /**
     * Select Collection
     * 
     * @param  string $database  
     * @param  string $collection
     * @return object            
     */
    public function selectCollection($database, $collection) {

        return $this->selectDB($database)->selectCollection($collection);
    }

    /**
     * Select database
     * 
     * @param  string $name
     * @return object
     */
    public function selectDB($name) {
        
        if(!isset($this->databases[$name])) {
            $this->databases[$name] = new Database($this->path.'/'.$name.'.sqlite', $this->options);
        }

        return $this->databases[$name];
    }

    public function __get($database) {
        
        return $this->selectDB($database);
    }
}