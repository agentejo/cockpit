<?php

namespace ColorThief;

/* Simple priority queue */
class PQueue
{
    private $contents = array();
    private $sorted = false;
    private $comparator = null;

    public function __construct($comparator)
    {
        $this->setComparator($comparator);
    }

    private function sort()
    {
        usort($this->contents, $this->comparator);
        $this->sorted = true;
    }

    public function push($object)
    {
        array_push($this->contents, $object);
        $this->sorted = false;
    }

    public function peek($index = null)
    {
        if (!$this->sorted) {
            $this->sort();
        }

        if ($index === null) {
            $index = $this->size() - 1;
        }

        return $this->contents[$index];
    }

    public function pop()
    {
        if (!$this->sorted) {
            $this->sort();
        }

        return array_pop($this->contents);
    }

    public function size()
    {
        return count($this->contents);
    }

    public function map($function)
    {
        return array_map($function, $this->contents);
    }

    public function setComparator($function)
    {
        $this->comparator = $function;
        $this->sorted = false;
    }

    public function debug()
    {
        if (!$this->sorted) {
            $this->sort();
        }

        return $this->contents;
    }
}
