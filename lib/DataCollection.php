<?php

/**
 * Class DataCollection
 */
class DataCollection implements \Iterator {

    protected $position = 0;
    protected $items;

    /**
     * @param $items
     * @return DataCollection
     */
    public static function create($items) {

        $collection = new self($items);

        return $collection;
    }

    /**
     * @param $items
     */
    public function __construct($items) {

        $this->items = $items;
    }

    /**
     * @return int
     */
    public function count() {
        return count($this->items);
    }

    /**
     * @return null
     */
    public function first() {
        return isset($this->items[0]) ? $this->items[0] : null;
    }

    /**
     * @return null
     */
    public function last() {
        return isset($this->items[0]) ? $this->items[count($this->items)-1] : null;
    }

    /**
     * @return DataCollection
     */
    public function reverse() {
        return $this->setItems(array_reverse($this->items));
    }

    /**
     * @param $number
     * @return DataCollection
     */
    public function limit($number) {

        $items = array_slice($this->items, 0, $number);

        return $this->setItems($items);
    }

    /**
     * @param $number
     * @return DataCollection
     */
    public function skip($number) {

        $items = array_slice($this->items, $number);

        return $this->setItems($items);
    }

    /**
     * @param $criteria
     * @return DataCollection
     */
    public function not($criteria) {

        return $this->filter("!({$criteria})");
    }

    /**
     * @param $criteria
     * @return DataCollection
     */
    public function filter($criteria) {

        if (is_string($criteria)) {
            if (!function_exists('create_function')) {
                eval('$criteria = function($item) { return ('.$criteria.'); };');
            } else {
                $criteria = create_function('$item', "return ({$criteria});");
            }
        }

        return $this->setItems(array_values(array_filter($this->items, $criteria)));
    }

    /**
     * @param $expr
     * @param int $dir
     * @return $this
     */
    public function sort($expr, $dir = 1) {

        $cache    = [];
        $params   = explode(',', $expr);

        $getValue = function($page, $expr) use($cache) {

            if (!function_exists('create_function')) {
                eval('$cache[$expr] = function($item) { return ('.$expr.'); };');
            } else {
                $cache[$expr] = create_function('$item', "return ({$expr});");
            }

            $value = $cache[$expr]($page);

            return $value;
        };


        $callback = function($a, $b) use($params, $getValue, $dir) {

            $result = 0;

            foreach ($params as $param) {

                $valA = $getValue($a, $param);
                $valB = $getValue($b, $param);

                if ($valA > $valB) {
                    $result = 1;
                } elseif ($valA < $valB) {
                    $result = -1;
                }

                if ($result !== 0) {

                    $result *= $dir;
                    break;
                }
            }

            return $result;
        };

        usort($this->items, $callback);

        $this->position = 0;

        return $this;
    }

    /**
     * @param $obj
     * @return bool|int
     */
    public function index($obj) {

        $uid = (string)$obj;

        foreach ($this->items as $index => $item) {
            if ((string)$item === $uid) {
                return $index;
            }
        }

        return false;
    }

    /**
     * @param $index
     * @return bool
     */
    public function eq($index) {
        return isset($this->items[$index]) ? $this->items[$index] : null;
    }

    /**
     * @param $items
     * @return DataCollection
     */
    protected function setItems($items) {

        $collection = new static($items, $this);

        return $collection;
    }

    /**
     * @param $limit
     * @param $current
     * @return \Pagination
     */
    public function pagination($limit = 5, $current = 1) {

        $pagination = new \Pagination($this->count(), $limit, $current);

        return $pagination;
    }

    /**
     * @return array
     */
    public function toArray() {

        $items = [];

        foreach ($this->items as $item) {
            $items[] = $item->toArray();
        }

        return $items;
    }

    /**
     * Iterator implementation
     */
    public function rewind() {
        if ($this->position !== false) $this->position = 0;
    }

    public function current() {
        return $this->items[$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
    }

    public function valid() {

        return isset($this->items[$this->position]);
    }
}
