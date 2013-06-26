<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

/**
* This iterator builds a collection filled with zeroes, except for the range it knows
* the paginator will query it for.
*/
class Monkeys_Iterator implements Iterator, Countable
{
    private $_numItems;
    private $_items;
    private $_index = 0;

    public function __construct($items, $numItems, $recordsPerPage, $page)
    {
        if ($items && $numItems > 1) {
            $this->_items = array_fill(0, $numItems- 1, 0);
        } else {
            $this->_items = array();
        }

        array_splice($this->_items, $recordsPerPage * ($page - 1), $recordsPerPage, $items);
        $this->_numItems = $numItems;
    }

    public function current()
    {
        return $this->_items[$this->_index];
    }

    public function key()
    {
        return $this->_index;
    }

    public function next()
    {
        $this->_index++;
    }

    public function rewind()
    {
        $this->_index = 0;
    }

    public function valid()
    {
        return isset($this->_items[$this->_index]);
    }

    public function count()
    {
        return $this->_numItems;
    }
}
