<?php

namespace Parm;

use Parm\Exception\ErrorException;

class Rows implements \Iterator
{
    protected $processor;
    protected $result;
    protected $count;
    protected $position;
    protected $cache = array();
    private $freed = false;

    public function __construct(\Parm\DatabaseProcessor $processor)
    {
        $this->processor = $processor;
        $this->result = $this->processor->getResult($processor->getSQL());
        $this->count = (int)$this->processor->getNumberOfRowsFromResult($this->result);
        $this->position = 0;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        if (array_key_exists($this->position, $this->cache)) {
            return $this->cache[$this->position];
        } else {
            return $this->cache[$this->position] = $this->processor->loadDataObject($this->result->fetch(\PDO::FETCH_ASSOC));
        }
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return ($this->position < $this->count);
    }

    public function toArray()
    {
        $data = array();

        foreach ($this as $row) {
            $data[] = (array)$row;
        }

        return $data;

    }

    public function toJson()
    {
        $data = array();

        foreach ($this as $row) {
            $data[] = $row->toJSON();
        }

        return $data;
    }

    private function isCacheFull()
    {
        return (count($this->cache) == $this->count);
    }

}
