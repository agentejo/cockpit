<?php

class ContainerArray implements  ArrayAccess, Countable, IteratorAggregate {

    protected $props  = array();
    protected $bindTo = array();

    public function __construct($array = [], $bindTo = null) {

        $this->props  = new \ArrayObject($array);
        $this->bindTo = $bindTo ? $bindTo : $this;
    }

    public function extend() {

        $args  = func_get_args();
        $array = [];

        switch(count($args)){
            case 1:
                $array = $args[0];
                break;
            case 2:
                $array[$args[0]] = $args[1];
                break;
        }

        foreach($array as $name => $value) {

            if ($value instanceof \Closure) {
                $value = $value->bindTo($this->bindTo, $this->bindTo);
            }

            $this->props[$name] = $value;
        }

        return $this;
    }

    public function get($key, $default=null) {
        return $this->fetch_from_array($key, $default);
    }

    public function count() {
        return count($this->props);
    }

    public function getIterator() {
        return clone $this->props;
    }

    public function __set($name , $value) {

        $this->extend(array($name => $value));
    }
    public function __get($name) {
        return isset($this->props[$name]) ? $this->props[$name] :null;
    }
    public function __isset($name) {
        return isset($this->props[$name]);
    }
    public function __unset($name) {
        unset($this->props[$name]);
    }
    public function __call($name, $arguments) {

        if(isset($this->props[$name]) && is_callable($this->props[$name])) {
            return call_user_func_array($this->props[$name], $arguments);
        }

        if(isset($this->props['__call']) && is_callable($this->props['__call'])) {
            return call_user_func_array($this->props['__call'], $arguments);
        }

        return null;
    }

    // Array Access implementation

    public function offsetSet($key, $value) {

        if (strpos($key, '/')) {

            $keys = explode('/', $key);
            $mem  = $this->props;

            foreach($keys as $keyname) {

                if (!isset($mem[$keyname])) {
                    $mem[$keyname] = new ArrayObject([]);
                }

                $mem = &$mem[$keyname];
            }

            $mem = $value;

        } else {
            $this->props[$key] = $value;
        }
    }

    public function offsetGet($key) {

        return $this->get($key, null);
    }

    public function offsetExists($key) {
        return $this->get($key, null)===null ? false : true;
    }

    public function offsetUnset($key) {

        if (isset($this->props[$key])) {

            unset($this->props[$key]);

        } elseif (strpos($key, '/')) {

            $keys = explode('/', $key);

            switch(count($keys)){

                case 1:
                    if (isset($this->props[$keys[0]])){
                        unset($this->props[$keys[0]]);
                    }
                    break;

                case 2:
                    if (isset($this->props[$keys[0]][$keys[1]])){
                        unset($this->props[$keys[0]][$keys[1]]);
                    }
                    break;

                case 3:
                    if (isset($this->props[$keys[0]][$keys[1]][$keys[2]])){
                        unset($this->props[$keys[0]][$keys[1]][$keys[2]]);
                    }
                    break;

                case 4:
                    if (isset($this->props[$keys[0]][$keys[1]][$keys[2]][$keys[3]])){
                        unset($this->props[$keys[0]][$keys[1]][$keys[2]][$keys[3]]);
                    }
                    break;
                case 5:
                    if (isset($this->props[$keys[0]][$keys[1]][$keys[2]][$keys[3]][$keys[4]])){
                        unset($this->props[$keys[0]][$keys[1]][$keys[2]][$keys[3]][$keys[4]]);
                    }
                    break;
            }
        }
    }

    protected function fetch_from_array($index=null, $default = null) {

        if (is_null($index)) {

            return $default;

        } elseif (isset($this->props[$index])) {

            return $this->props[$index];

        } elseif (strpos($index, '/')) {

            $keys = explode('/', $index);

            switch(count($keys)){

                case 1:
                    if (isset($this->props[$keys[0]])){
                        return $this->props[$keys[0]];
                    }
                    break;

                case 2:
                    if (isset($this->props[$keys[0]][$keys[1]])){
                        return $this->props[$keys[0]][$keys[1]];
                    }
                    break;

                case 3:
                    if (isset($this->props[$keys[0]][$keys[1]][$keys[2]])){
                        return $this->props[$keys[0]][$keys[1]][$keys[2]];
                    }
                    break;

                case 4:
                    if (isset($this->props[$keys[0]][$keys[1]][$keys[2]][$keys[3]])){
                        return $this->props[$keys[0]][$keys[1]][$keys[2]][$keys[3]];
                    }
                    break;
                case 5:
                    if (isset($this->props[$keys[0]][$keys[1]][$keys[2]][$keys[3]][$keys[4]])){
                        return $this->props[$keys[0]][$keys[1]][$keys[2]][$keys[3]][$keys[4]];
                    }
                    break;
            }
        }

        return $default;
    }
}