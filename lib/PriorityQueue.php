<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class PriorityQueue implements Countable, IteratorAggregate {
    
	const EXTR_DATA     = 0x00000001;
    const EXTR_PRIORITY = 0x00000002;
    const EXTR_BOTH     = 0x00000003;

    protected $queue;
    protected $items = array();
    protected $extractMode;
    
    public function __construct() {
        $this->queue = new SplPriorityQueue;
        $this->extractMode = self::EXTR_DATA;
    }

    public function count() {
        return count($this->items);
    }

    public function isEmpty() {
        return (0 === $this->count());
    }

    public function setExtractFlags($flags) {
    	
    	$this->extractMode = $flags;
    	$this->queue->setExtractFlags($flags);
    }

    public function insert($item, $priority) {
        
        $priority      = (int) $priority;
        $this->items[] = array('data' => $item, 'priority' => $priority);

        $this->queue->insert($item, $priority);

        return $this;
    }

    public function contains($data) {
        foreach ($this->items as $item) {
            if ($item['data'] === $data) {
                return true;
            }
        }
        return false;
    }

    public function hasPriority($priority) {
        foreach ($this->items as $item) {
            if ($item['priority'] === $priority) {
                return true;
            }
        }
        return false;
    }

    public function remove($item) {
        
        foreach ($this->items as $key => $item) {

            if ($item['data'] === $item) {
                
                unset($this->items[$key]);
	            
	            $this->queue = new SplPriorityQueue;
	            $this->queue->setExtractFlags($this->extractMode);

	            foreach ($this->items as $item) {
	                $this->queue->insert($item['data'], $item['priority']);
	            }
	            return true;
            }
        }

        return false;
    }

    public function toArray($flag = self::EXTR_DATA) {
        switch ($flag) {
            case self::EXTR_BOTH:
                return $this->items;
                break;
            case self::EXTR_PRIORITY:
                return array_map(function ($item) {
                    return $item['priority'];
                }, $this->items);
            case self::EXTR_DATA:
            default:
                return array_map(function ($item) {
                    return $item['data'];
                }, $this->items);
        }
    }

    public function top() {
        return $this->getIterator()->top();
    }

    public function extract() {
        return $this->queue->extract();
    }
    
    public function getIterator() {
        return clone $this->queue;
    }

    public function __clone() {

    	// enables deep cloning
        $this->queue = clone $this->queue;
    }
}