<?php

class ContainerArray implements  ArrayAccess, Countable, IteratorAggregate, JsonSerializable {

    protected $props  = array();
    protected $bindTo = array();

    public function __construct($array = [], $bindTo = null) {

        $this->props  = new \ArrayObject(is_array($array) ? $array:[]);
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

    public function toArray() {
        return $this->props;
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

    /**
     * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
     * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
     *
     * Source: Utility/Hash.php
     */

    public function extract($path) {

        if (empty($path)) {
            return $this->props;
        }
        // Simple paths.
        if (!preg_match('/[{\[]/', $path)) {
            return (array)$this->get($path);
        }
        if (strpos($path, '[') === false) {
            $tokens = explode('/', $path);
        } else {
            $tokens = $this->_tokenize($path, '/', '[', ']');
        }
        $_key = '__set_item__';
        $context = array($_key => array($this->props));
        foreach ($tokens as $token) {
            $next = array();
            list($token, $conditions) = $this->_splitConditions($token);
            foreach ($context[$_key] as $item) {
                foreach ((array)$item as $k => $v) {
                    if ($this->_matchToken($k, $token)) {
                        $next[] = $v;
                    }
                }
            }
            // Filter for attributes.
            if ($conditions) {
                $filter = array();
                foreach ($next as $item) {
                    if (is_array($item) && $this->_matches($item, $conditions)) {
                        $filter[] = $item;
                    }
                }
                $next = $filter;
            }
            $context = array($_key => $next);
        }
        return $context[$_key];
    }

    protected function _tokenize($data, $separator = ',', $leftBound = '(', $rightBound = ')') {

        if (empty($data)) {
            return array();
        }

        $depth    = 0;
        $offset   = 0;
        $buffer   = '';
        $results  = array();
        $length   = strlen($data);
        $open     = false;

        while ($offset <= $length) {
            $tmpOffset = -1;
            $offsets   = array(
                strpos($data, $separator, $offset),
                strpos($data, $leftBound, $offset),
                strpos($data, $rightBound, $offset)
            );

            for ($i = 0; $i < 3; $i++) {
                if ($offsets[$i] !== false && ($offsets[$i] < $tmpOffset || $tmpOffset == -1)) {
                    $tmpOffset = $offsets[$i];
                }
            }

            if ($tmpOffset !== -1) {
                $buffer .= substr($data, $offset, ($tmpOffset - $offset));
                if (!$depth && $data{$tmpOffset} === $separator) {
                    $results[] = $buffer;
                    $buffer = '';
                } else {
                    $buffer .= $data{$tmpOffset};
                }
                if ($leftBound !== $rightBound) {
                    if ($data{$tmpOffset} === $leftBound) {
                        $depth++;
                    }
                    if ($data{$tmpOffset} === $rightBound) {
                        $depth--;
                    }
                } else {
                    if ($data{$tmpOffset} === $leftBound) {
                        if (!$open) {
                            $depth++;
                            $open = true;
                        } else {
                            $depth--;
                        }
                    }
                }
                $offset = ++$tmpOffset;
            } else {
                $results[] = $buffer . substr($data, $offset);
                $offset = $length + 1;
            }
        }
        if (empty($results) && !empty($buffer)) {
            $results[] = $buffer;
        }
        if (!empty($results)) {
            return array_map('trim', $results);
        }
        return array();
    }

    protected function _splitConditions($token) {
        $conditions = false;
        $position = strpos($token, '[');
        if ($position !== false) {
            $conditions = substr($token, $position);
            $token = substr($token, 0, $position);
        }
        return array($token, $conditions);
    }

    protected function _matchToken($key, $token) {
        if ($token === '{n}') {
            return is_numeric($key);
        }
        if ($token === '{s}') {
            return is_string($key);
        }
        if (is_numeric($token)) {
            return ($key == $token);
        }
        return ($key === $token);
    }

    protected function _matches(array $data, $selector) {

        preg_match_all(
            '/(\[ (?P<attr>[^=><!]+?) (\s* (?P<op>[><!]?[=]|[><]) \s* (?P<val>(?:\/.*?\/ | [^\]]+)) )? \])/x',
            $selector,
            $conditions,
            PREG_SET_ORDER
        );

        foreach ($conditions as $cond) {
            $attr = $cond['attr'];
            $op = isset($cond['op']) ? $cond['op'] : null;
            $val = isset($cond['val']) ? $cond['val'] : null;
            // Presence test.
            if (empty($op) && empty($val) && !isset($data[$attr])) {
                return false;
            }
            // Empty attribute = fail.
            if (!(isset($data[$attr]) || array_key_exists($attr, $data))) {
                return false;
            }
            $prop = null;
            if (isset($data[$attr])) {
                $prop = $data[$attr];
            }
            $isBool = is_bool($prop);
            if ($isBool && is_numeric($val)) {
                $prop = $prop ? '1' : '0';
            } elseif ($isBool) {
                $prop = $prop ? 'true' : 'false';
            }
            // Pattern matches and other operators.
            if ($op === '=' && $val && $val[0] === '/') {
                if (!preg_match($val, $prop)) {
                    return false;
                }
            } elseif (($op === '=' && $prop != $val) ||
                ($op === '!=' && $prop == $val) ||
                ($op === '>' && $prop <= $val) ||
                ($op === '<' && $prop >= $val) ||
                ($op === '>=' && $prop < $val) ||
                ($op === '<=' && $prop > $val)
            ) {
                return false;
            }
        }
        return true;
    }

    public function jsonSerialize() {
        return $this->props;
    }

}
